<?php

namespace lhs\craftpageexporter\models\transformers;


use Craft;
use lhs\craftpageexporter\helpers\PhpUri;
use lhs\craftpageexporter\models\Asset;
use lhs\craftpageexporter\models\ImageAsset;
use lhs\craftpageexporter\models\MiscAsset;
use lhs\craftpageexporter\models\ScriptAsset;
use lhs\craftpageexporter\models\StyleAsset;

class PrefixExportUrlTransformer extends BaseTransformer
{
    /** @var string Prefix to append to export URL */
    public $prefix = '';

    /** @var array Only theses Asset type will be prefixed */
    protected $assetClasses = [
        ImageAsset::class,
        MiscAsset::class,
        ScriptAsset::class,
        StyleAsset::class,
    ];

    /**
     * @param Asset $asset
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function transform($asset)
    {
        if (!$this->needToTransform($asset)) {
            return;
        }

        $prefix = Craft::$app->getView()->renderObjectTemplate($this->prefix, $asset->initiatorEntry, [
            'year'  => date('Y'),
            'month' => date('m'),
            'day'   => date('d'),
        ]);

        // Set the new url to the asset
        $asset->exportUrl = PhpUri::parse($prefix)->join($asset->exportUrl);

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