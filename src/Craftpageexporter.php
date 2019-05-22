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
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\elements\Entry;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use lhs\craftpageexporter\assetbundles\CraftpageexporterEntryEditAssetBundle;
use lhs\craftpageexporter\assetbundles\CraftpageexporterSettingsAssetBundle;
use lhs\craftpageexporter\elements\actions\CraftpageexporterElementAction;
use lhs\craftpageexporter\models\Settings;
use lhs\craftpageexporter\models\transformers\AssetTransformer;
use lhs\craftpageexporter\models\transformers\FlattenTransformer;
use lhs\craftpageexporter\models\transformers\PrefixExportUrlTransformer;
use lhs\craftpageexporter\services\CraftpageexporterService;
use Twig\Parser;
use yii\base\Event;

/**
 * Class Craftpageexporter
 *
 * @author    La Haute Société
 * @package   Craftpageexporter
 * @since     1.0.0
 *
 * @property  CraftpageexporterService $craftpageexporterService
 */
class Craftpageexporter extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Craftpageexporter
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'lhs\craftpageexporter\console\controllers';
        }

        Craft::$app->view->hook('cp.entries.edit', function (&$context) {
            $this->view->registerAssetBundle(CraftpageexporterEntryEditAssetBundle::class);
        });

        // Handler: CraftVariable::EVENT_INIT
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pageExporter', PageExporterVariable::class);
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                Craft::warning('REGISTER URL EXPORTER');
                $event->rules['page-exporter/export/entry-<entryIds:\d+(,\d+)*>/site-<siteId:\d+>'] = 'craft-page-exporter/default/export';
            }
        );

        // Register element action to assets for clearing transforms
        Event::on(Entry::class, Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions[] = CraftpageexporterElementAction::class;
            }
        );

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
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\SyntaxError
     */
    public function getExportConfig($overrides = [])
    {
        /** @var Settings $settings */
        $settings = $this->getSettings();
        $settings = $this->overridesSettings($settings, $overrides);

        $exportConfig = [
            'baseUrl'       => $settings->baseUrl,
            'inlineStyles'  => $settings->inlineStyles,
            'inlineScripts' => $settings->inlineScripts,
            'transformers'  => [],
        ];

        if ($settings->flatten === true) {
            $exportConfig['transformers'][] = new FlattenTransformer();
        }

        if ($settings->prefixExportUrl) {
            $exportConfig['transformers'][] = new PrefixExportUrlTransformer([
                'prefix' => $settings->prefixExportUrl,
            ]);
        }

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
