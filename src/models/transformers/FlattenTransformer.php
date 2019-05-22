<?php

namespace lhs\craftpageexporter\models\transformers;


use lhs\craftpageexporter\models\Asset;
use lhs\craftpageexporter\models\ImageAsset;
use lhs\craftpageexporter\models\MiscAsset;
use lhs\craftpageexporter\models\ScriptAsset;
use lhs\craftpageexporter\models\StyleAsset;

class FlattenTransformer extends BaseTransformer
{
    /** @var bool Append hash to filename */
    public $appendHash = true;

    /** @var array Only theses Asset type will be flattened */
    protected $assetClasses = [
        ImageAsset::class,
        MiscAsset::class,
        ScriptAsset::class,
        StyleAsset::class,
    ];

    /**
     * @param Asset $asset
     * @throws \ReflectionException
     */
    public function transform($asset)
    {
        if (!$this->needToTransform($asset)) {
            return;
        }

        // Filename components
        $filename = pathinfo($asset->url, PATHINFO_FILENAME);
        $extension = pathinfo($asset->url, PATHINFO_EXTENSION);

        // Hash content ?
        $hash = '';
        if ($this->appendHash) {
            $hash = '-' . hash('crc32', $asset->getContent());
        }

        // Reconstruct filename
        $fullName = $filename . $hash . '.' . $extension;

        // Set the new url to the asset
        $asset->exportUrl = $fullName;
        $asset->exportPath = $fullName;

        $asset->updateInitiatorContent();
    }

    /**
     * Return true if $asset class name is in assetClasses
     * @param Asset $asset
     * @return bool
     * @throws \ReflectionException
     */
    protected function needToTransform($asset)
    {
        if (!$asset->isInBaseUrl()) {
            return false;
        }

        $class = new \ReflectionClass($asset);
        $className = $class->getName();

        return in_array($className, $this->assetClasses);
    }
}