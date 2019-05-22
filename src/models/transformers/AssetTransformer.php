<?php

namespace lhs\craftpageexporter\models\transformers;


use Craft;
use lhs\craftpageexporter\models\Asset;

class AssetTransformer extends BaseTransformer
{
    /** @var null|callable */
    public $transformer = null;

    /**
     * @param Asset $asset
     */
    public function transform($asset)
    {
        if (!is_callable($this->transformer)) {
            return;
        }

        ($this->transformer)($asset);
    }

}