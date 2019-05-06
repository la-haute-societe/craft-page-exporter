<?php

namespace lhs\craftpageexporter\models\transformers;


use lhs\craftpageexporter\models\Asset;
use yii\base\Component;

class BaseExportPathTransformer extends Component
{

    /**
     * @param Asset $asset
     * @return string
     */
    public function transform($asset)
    {
        $asset->exportPath = $asset->getRelativePath();
        $asset->exportUrl = $asset->getRelativeUrl();
    }
}