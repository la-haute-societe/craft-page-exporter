<?php
/**
 * craft-page-exporter plugin for Craft CMS 3.x
 *
 * Craft page exporter
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2019 La Haute Société
 */

namespace lhs\craftpageexporter;

use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\elements\Entry;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\App;
use craft\helpers\Json;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use lhs\craftpageexporter\assetbundles\CpAssetBundle;
use lhs\craftpageexporter\elements\actions\ExportElementAction;
use lhs\craftpageexporter\models\Asset;
use lhs\craftpageexporter\models\Settings;
use lhs\craftpageexporter\models\transformers\AssetTransformer;
use lhs\craftpageexporter\models\transformers\PathAndUrlTransformer;
use lhs\craftpageexporter\services\Context;
use lhs\craftpageexporter\services\Assets;
use lhs\craftpageexporter\services\Export;
use lhs\craftpageexporter\variables\PageExporterVariable;
use yii\base\Event;
use nystudio107\pluginvite\services\VitePluginService;
use yii\base\InvalidConfigException;

/**
 * @author   La Haute Société
 * @package  Craftpageexporter
 * @since    1.0.0
 *
 * @property Context $context
 * @property Assets $assets
 * @property Export $export
 * @property VitePluginService $vite
 * @method Settings getSettings()
 */
class Plugin extends BasePlugin
{
    public static Plugin $plugin;

    public bool $hasCpSettings = true;

    public function __construct($id, $parent = null, array $config = [])
    {
        $config['components'] = [
            'export'  => Export::class,
            'context' => Context::class,
            'assets'  => Assets::class,
            'vite'    => [
                'class' => VitePluginService::class,
                'assetClass' => CpAssetBundle::class,
                'useDevServer' => true,
                'devServerPublic' => 'http://localhost:3001',
                'serverPublic' => App::env('ALIAS_WEB'),
                'errorEntry' => 'src/js/app.ts',
                'devServerInternal' => 'http://host.docker.internal:3001',
                'checkDevServer' => true,
            ],
        ];
        parent::__construct($id, $parent, $config);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        if (Craft::$app->getRequest()->isCpRequest) {
            $this->vite->register('src/js/main.js', false, [
                'depends' => [
                    CpAssetBundle::class,
                ],
            ]);
        }

        if ($this->context->isInExportContext()) {
            Craft::$app->getConfig()->getGeneral()->generateTransformsBeforePageLoad = true;
        }

        // Register variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pageExporter', [
                    'class' => PageExporterVariable::class,
                    'viteService' => $this->vite,
                ]);
            }
        );

        // Register routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            static function (RegisterUrlRulesEvent $event) {
                $event->rules['page-exporter/export/entry-<entryIds:\d+(,\d+)*>/site-<siteId:\d+>'] = 'craft-page-exporter/default/export';
                $event->rules['page-exporter/analyze/entry-<entryIds:\d+(,\d+)*>/site-<siteId:\d+>'] = 'craft-page-exporter/default/analyze';
            }
        );

        // Register element action to export entries
        Event::on(Entry::class, Element::EVENT_REGISTER_ACTIONS,
            static function (RegisterElementActionsEvent $event) {
                $event->actions[] = ExportElementAction::class;
            }
        );

        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            static function (RegisterUserPermissionsEvent $event) {
                $event->permissions['Page exporter'] = [
                    'pageExporter.export' => [
                        'label' => 'Export entries',
                    ],
                ];
            }
        );

        Event::on(View::class, View::EVENT_END_BODY, function () {
            if (!$this->context->isInExportContext()) {
                return;
            }

            $assets = $this->assets->getRegisteredAssets();
            if (empty($assets)) {
                return;
            }

            printf(
                '<page-exporter-registered-assets>%s</page-exporter-registered-assets>',
                Json::encode(array_map(static function(Asset $asset) {
                    return [
                        'sourcePath' => $asset->getSourcePath(),
                        'exportPath' => $asset->getExportPath(),
                    ];
                }, $assets))
            );
        });

        Craft::info(
            Craft::t(
                'craft-page-exporter',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * @param array $overrides
     * @return array
     */
    public function getExportConfig(array $overrides = []): array
    {
        /** @var Settings $settings */
        $settings = $this->getSettings();
        $settings = $this->overridesSettings($settings, $overrides);

        $exportConfig = [
            'baseUrl'               => Craft::getAlias('@web'),
            'inlineStyles'          => $settings->inlineStyles,
            'inlineScripts'         => $settings->inlineScripts,
            'customSelectors'       => $settings->customSelectors,
            'sourcePathTransformer' => $settings->sourcePathTransformer,
            'failOnFileNotFound'    => $settings->failOnFileNotFound,
            'transformers'          => [],
        ];

        // Path and URL transformer
        $pathAndUrlTransformerConfig = [];
        if (!empty($settings->exportUrlFormat)) {
            $pathAndUrlTransformerConfig['exportUrlFormat'] = $settings->exportUrlFormat;
        }
        if (!empty($settings->exportPathFormat)) {
            $pathAndUrlTransformerConfig['exportPathFormat'] = $settings->exportPathFormat;
        }
        $exportConfig['transformers'][] = new PathAndUrlTransformer($pathAndUrlTransformerConfig);

        // Custom transformers
        if (!is_null($settings->assetTransformers)) {
            if (!is_array($settings->assetTransformers)) {
                $settings->assetTransformers = [$settings->assetTransformers];
            }

            foreach ($settings->assetTransformers as $assetTransformer) {
                $exportConfig['transformers'][] = new AssetTransformer([
                    'transformer' => $assetTransformer,
                ]);
            }
        }

        return $exportConfig;
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        // Get and pre-validate the settings
        $settings = $this->getSettings();
        $settings->validate();

        // Get the settings that are being defined by the config file
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

        return Craft::$app->view->renderTemplate('craft-page-exporter/settings', [
            'settings'  => $this->getSettings(),
            'overrides' => $this->array_keys_multi($overrides),
        ]);
    }


    /**
     * @param array  $array
     * @param string $prefix
     * @return array
     */
    private function array_keys_multi(array $array, string $prefix = ""): array
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $keys[] = $prefix . $key;
            if (is_array($value)) {
                $keys = array_merge($keys, $this->array_keys_multi($value, sprintf('%s%s.', $prefix, $key)));
            }
        }

        return $keys;
    }

    /**
     * @param Settings $settings
     * @param array $overrides
     * @return Settings
     */
    private function overridesSettings(Settings $settings, array $overrides = []): Settings
    {
        foreach ($settings as $key => $value) {
            if (isset($overrides[$key])) {
                $settings->$key = $overrides[$key];
            }
        }

        return $settings;
    }
}
