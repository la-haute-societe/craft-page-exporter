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
use lhs\craftpageexporter\Craftpageexporter;
use venveo\bulkedit\assetbundles\bulkeditelementaction\BulkEditElementActionAsset;

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
        $currentSiteId = Craft::$app->sites->getCurrentSite()->id;

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
            var elementIds = $(\$selectedItems).map(function(){
                return $(this).data('id');
            }).get();
            
            // Get action URL
            var url = Craft.getUrl('page-exporter/export/entry-'+elementIds.join()+'/site-{$currentSiteId}');
            var fixedUrl = url.replace('admin/', '');
            
            Craft.redirectTo(fixedUrl);
        }
    });
})();
EOD;

        $view = Craft::$app->getView();
        $view->registerJs($js);
    }
}
