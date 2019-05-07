<?php
/**
 * Imager plugin for Craft CMS 3.x
 *
 * Image transforms gone wild
 *
 * @link      https://www.vaersaagod.no
 * @copyright Copyright (c) 2018 André Elvan
 */

namespace lhs\craftpageexporter\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\helpers\Json;
use lhs\craftpageexporter\assetbundles\CraftpageexporterExportModalAssetBundle;

class CraftpageexporterElementAction extends ElementAction
{
    public $label;

    public function init()
    {
        if ($this->label === null) {
            $this->label = 'Export';
        }
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return $this->label;
    }


    /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        $type = Json::encode(static::class);

        $js = <<<EOD
(function()
{
    var trigger = new Craft.ElementActionTrigger({
        type: {$type},
        batch: true,
        validateSelection: function(\$selectedItems)
        {
            return \$selectedItems.length != 0;
        },
        activate: function(\$selectedItems)
        {
            // Get parameters
            var settings = {};
            var entryIds = $(\$selectedItems).map(function(){
                return $(this).data('id');
            }).get();
            console.log(entryIds);
            entryIds = entryIds.join(",");
            var modal = new Craft.CraftpageexporterExportModal(entryIds, settings);
        }
    });
})();
EOD;

        $view = Craft::$app->getView();
        $view->registerJs($js);
        $view->registerAssetBundle(CraftpageexporterExportModalAssetBundle::class);
    }
}
