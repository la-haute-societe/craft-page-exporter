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
use craft\elements\Entry;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use lhs\craftpageexporter\assetbundles\CraftpageexporterEntryEditAssetBundle;
use lhs\craftpageexporter\assetbundles\CraftpageexporterSettingsAssetBundle;
use lhs\craftpageexporter\elements\actions\ExportElementAction;
use lhs\craftpageexporter\models\Settings;
use lhs\craftpageexporter\models\transformers\AssetTransformer;
use lhs\craftpageexporter\models\transformers\PathAndUrlTransformer;
use lhs\craftpageexporter\services\Context;
use lhs\craftpageexporter\services\Assets;
use lhs\craftpageexporter\services\Export;
use lhs\craftpageexporter\variables\PageExporterVariable;
use yii\base\Event;

/**
 * Class Craftpageexporter
 *
 * @author    La Haute Société
 * @package   Craftpageexporter
 * @since     1.0.0
 *
 * @property  Context $context
 * @property  Assets $assets
 * @property  Export $export
 */
class Plugin extends \craft\base\Plugin
{
    /** @var Plugin */
    public static $plugin;

    /** @var bool */
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register asset bundle
        Craft::$app->view->hook('cp.entries.edit', function (&$context) {
            $this->view->registerAssetBundle(CraftpageexporterEntryEditAssetBundle::class);
        });

        // Register variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pageExporter', PageExporterVariable::class);
            }
        );

        // Register routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['page-exporter/export/entry-<entryIds:\d+(,\d+)*>/site-<siteId:\d+>'] = 'craft-page-exporter/default/export';
                $event->rules['page-exporter/analyze/entry-<entryIds:\d+(,\d+)*>/site-<siteId:\d+>'] = 'craft-page-exporter/default/analyze';
            }
        );

        // Register element action to export entries
        Event::on(Entry::class, Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions[] = ExportElementAction::class;
            }
        );

        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function (RegisterUserPermissionsEvent $event) {
                $event->permissions['Page exporter'] = [
                    'pageExporter.export' => [
                        'label' => 'Export entries',
                    ],
                ];
            }
        );

        // Info
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
    public function getExportConfig($overrides = [])
    {
        /** @var Settings $settings */
        $settings = $this->getSettings();
        $settings = $this->overridesSettings($settings, $overrides);

        $exportConfig = [
            'baseUrl'               => UrlHelper::baseRequestUrl(),
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

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        // Get and pre-validate the settings
        $settings = $this->getSettings();
        $settings->validate();

        // Get the settings that are being defined by the config file
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

        Craft::$app->view->registerAssetBundle(CraftpageexporterSettingsAssetBundle::class);

        return Craft::$app->view->renderTemplate('craft-page-exporter/settings', [
            'settings'  => $this->getSettings(),
            'overrides' => $this->array_keys_multi($overrides),
        ]);
    }



    // Private Methods
    // =========================================================================

    /**
     * @param array  $array
     * @param string $prefix
     * @return array
     */
    private function array_keys_multi(array $array, $prefix = "")
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
     * @param array    $overrides
     * @return Settings
     */
    private function overridesSettings($settings, $overrides = [])
    {
        foreach ($settings as $key => $value) {
            if (isset($overrides[$key])) {
                $settings->$key = $overrides[$key];
            }
        }

        return $settings;
    }
}
