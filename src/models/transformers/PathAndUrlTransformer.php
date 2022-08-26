<?php

namespace lhs\craftpageexporter\models\transformers;


use Craft;
use lhs\craftpageexporter\models\Asset;
use lhs\craftpageexporter\models\ImageAsset;
use lhs\craftpageexporter\models\MiscAsset;
use lhs\craftpageexporter\models\ScriptAsset;
use lhs\craftpageexporter\models\StyleAsset;
use Throwable;
use yii\base\Exception;

class PathAndUrlTransformer extends BaseTransformer
{
    public string $exportUrlFormat = '';
    public string $exportPathFormat = '';

    /** @var array Only these Asset type will be flattened */
    protected array $assetClasses = [
        ImageAsset::class,
        MiscAsset::class,
        ScriptAsset::class,
        StyleAsset::class,
    ];

    /**
     * @param Asset $asset
     * @throws Throwable
     * @throws Exception
     */
    public function transform($asset): void
    {
        if (!$this->needToTransform($asset)) {
            return;
        }

        // Get URL of this asset relative to root asset instead of its initiator
        // because "url format" is relative to the root asset (root of the archive)
        $absoluteUrl = $asset->calculateAbsoluteUrl($asset->getBaseUrl(), $asset->url);
        $urlRelativeToRootAsset = $asset->calculateRelativePath($asset->getRootAsset()->getBaseUrl(),
            $absoluteUrl);

        // Filename components
        $parseUrl = parse_url($urlRelativeToRootAsset);
        $pathinfo = pathinfo($parseUrl['path']);
        $filename = $pathinfo['filename'] ?? '';
        $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
        $basename = $pathinfo['basename'] ?? '';
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


        // Render URL format with the variables above.
        $exportUrl = Craft::$app->getView()->renderObjectTemplate($this->exportUrlFormat, null, $variables);
        $exportPath = Craft::$app->getView()->renderObjectTemplate($this->exportPathFormat, null, $variables);

        // So, here we got the new URL of the asset relative to the root asset, but want the URL relative
        // to its initiator. For that we have to calculate first the absolute URL.
        // -----------------------------------------------------------------------------------------------
        // Calculate absolute URL of the new path.
        $absoluteNewUrl = $asset->calculateAbsoluteUrl($asset->getRootAsset()->getBaseUrl(), $exportUrl);
        $absoluteNewPath = $asset->calculateAbsoluteUrl($asset->getRootAsset()->getBaseUrl(), $exportPath);

        // Calculate URL relative to initiator URL from the absolute URL.
        $newUrlRelativeToInitiator = $asset->calculateRelativePath($asset->getBaseUrl(), $absoluteNewUrl);
        $newUrlRelativeToRootAsset = $asset->calculateRelativePath(
            $asset->getRootAsset()->getBaseUrl(),
            $absoluteNewPath
        );

        // Set the new url and path to our asset.
        // -------------------------------------
        // Url is relative to its initiator.
        $asset->setExportUrl($newUrlRelativeToInitiator);

        // Path is relative to the root of the archive.
        $asset->setExportPath($newUrlRelativeToRootAsset);
    }

    /**
     * Return true if $asset class name is in assetClasses and in base url.
     *
     * @param Asset $asset
     * @return bool
     */
    protected function needToTransform(Asset $asset): bool
    {
        if (!$asset->willBeInArchive) {
            return false;
        }

        $class = new \ReflectionClass($asset);
        return in_array($class->getName(), $this->assetClasses, true);
    }
}
