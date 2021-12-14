<?php

namespace lhs\craftpageexporter\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\helpers\Json;

class ExportElementAction extends ElementAction
{
    public $label;

    public function init()
    {
        if ($this->label === null) {
            $this->label = Craft::t('craft-page-exporter', 'Export');
        }
    }

    public static function isDownload(): bool
    {
        return true;
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
            var entryIds = $(\$selectedItems).map(function(){
                return $(this).data('id');
            }).get();
            entryIds = entryIds.join(",");

            var modal = new Craft.CraftpageexporterExportModal(entryIds);
        }
    });
})();
EOD;
    }
}
