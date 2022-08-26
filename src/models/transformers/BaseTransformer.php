<?php

namespace lhs\craftpageexporter\models\transformers;

use lhs\craftpageexporter\models\Asset;
use yii\base\Component;

abstract class BaseTransformer extends Component
{
    /**
     * @param Asset $asset
     */
    abstract public function transform(Asset $asset): void;
}
