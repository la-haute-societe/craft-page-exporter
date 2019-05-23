<?php

namespace lhs\craftpageexporter\models\transformers;


use Craft;
use lhs\craftpageexporter\models\Asset;
use lhs\craftpageexporter\models\ImageAsset;
use lhs\craftpageexporter\models\MiscAsset;
use lhs\craftpageexporter\models\ScriptAsset;
use lhs\craftpageexporter\models\StyleAsset;

class PathAndUrlTransformer extends BaseTransformer
{
    public $exportUrlFormat = '';
    public $exportPathFormat = '';

    /** @var array Only theses Asset type will be flattened */
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

        // Filename components
        $parseUrl = parse_url($asset->url);
        $pathinfo = pathinfo($parseUrl['path']);
        $filename = isset($pathinfo['filename']) ? $pathinfo['filename'] : '';
        $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
        $basename = isset($pathinfo['basename']) ? $pathinfo['basename'] : '';
        $dirname = isset($pathinfo['dirname']) ? ltrim($pathinfo['dirname'], '/') : '';

        // Hash content
        $hash = hash('crc32', $asset->getContent());

        // All variables that can be used to build the path
        $variables = [
            'filename'  => $filename,
            'extension' => $extension,
            'basename'  => $basename,
            'dirname'   => $dirname,
            'hash'      => $hash,
            'year'      => date('Y'),
            'month'     => date('m'),
            'day'       => date('d'),
            'hour'      => date('H'),
            'minute'    => date('i'),
            'second'    => date('s'),
        ];
// var_dump($this->exportUrlFormat);
// die();
        $exportUrl = Craft::$app->getView()->renderObjectTemplate($this->exportUrlFormat, null, $variables);
        $exportPath = Craft::$app->getView()->renderObjectTemplate($this->exportPathFormat, null, $variables);

        // Set the new url to the asset
        $asset->exportUrl = $exportUrl;
        $asset->exportPath = $exportPath;

        $asset->updateInitiatorContent();
    }

    /**
     * Return true if $asset class name is in assetClasses and in base url
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