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
use craft\web\twig\variables\CraftVariable;
use lhs\craftpageexporter\assetbundles\CraftpageexporterEntryEditAssetBundle;
use lhs\craftpageexporter\elements\actions\CraftpageexporterElementAction;
use lhs\craftpageexporter\models\Settings;
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
     * @return bool|\craft\base\Model|null
     */
    public function getExportConfig($overrides = [])
    {
        /** @var Settings $settings */
        $settings = $this->getSettings();

        $this->overridesSettings($settings, $overrides);

        foreach ($settings->transformers as $key => $options) {

            // Check if transformer is enabled
            if (!$options['enabled']) {
                unset($settings->transformers[$key]);
                continue;
            }

            $className = sprintf('lhs\craftpageexporter\models\transformers\%s', $key);

            // Remove 'enabled' from transformer options
            unset($options['enabled']);

            // Interpret twig
            foreach ($options as &$option) {
                $option = Craft::$app->getView()->renderString($option);
            }

            // Use options as transformer properties
            $settings->transformers[$key] = new $className($options);
        }

        return $settings;
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

        return Craft::$app->view->renderTemplate('craft-page-exporter/settings', [
            'settings' => $this->getSettings(),
            'overrides' => $this->array_keys_multi($overrides),
        ]);
    }



    // Private Methods
    // =========================================================================

    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    private function array_keys_multi(array $array, $prefix = "") {
        $keys = [];

        foreach ($array as $key => $value) {
            $keys[] = $prefix.$key;
            if (is_array($value)) {
                $keys = array_merge($keys, $this->array_keys_multi($value, sprintf('%s%s.', $prefix, $key)));
            }
        }

        return $keys;
    }

    /**
     * @param Settings $settings
     * @param array $overrides
     */
    private function overridesSettings(&$settings, $overrides) {
        foreach ($settings as $key => $value) {
            $settings->$key = $overrides[$key];
        }
    }
}
