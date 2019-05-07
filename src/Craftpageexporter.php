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
use lhs\craftpageexporter\models\transformers\FlattenTransformer;
use lhs\craftpageexporter\models\transformers\PrefixExportUrlTransformer;
use lhs\craftpageexporter\services\CraftpageexporterService;
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

        Craft::$app->view->hook('cp.entries.edit', function(&$context) {
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
            function(RegisterElementActionsEvent $event) {
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
    public function getExportConfig($options = [])
    {
        $settings = $this->getSettings();
        foreach ($options as $key => $value) {
            $settings->$key = $value;
        }

        foreach ($settings->transformers as &$transformer) {
            switch ($transformer['type']) {
                case 'flatten':
                    $transformer = new FlattenTransformer();
                    break;
                case 'prefix':
                    $transformer = new PrefixExportUrlTransformer(['prefix' => $transformer['prefix']]);
            }
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
        return Craft::$app->view->renderTemplate(
            'craft-page-exporter/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
