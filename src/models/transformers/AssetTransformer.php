<?php

namespace lhs\craftpageexporter\models\transformers;

use lhs\craftpageexporter\models\Asset;

class AssetTransformer extends BaseTransformer
{
    /** @var null|callable */
    public $transformer = null;

    /**
     * @param Asset $asset
     */
    public function transform(Asset $asset): void
    {
        if (!is_callable($this->transformer)) {
            return;
        }

        ($this->transformer)($asset);
    }

}
