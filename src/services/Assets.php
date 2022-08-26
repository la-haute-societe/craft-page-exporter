<?php
/**
 * craft-page-exporter plugin for Craft CMS 3.x
 *
 * Craft page exporter
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2019 La Haute Société
 */

namespace lhs\craftpageexporter\services;

use Craft;
use craft\base\Component;
use Exception;
use lhs\craftpageexporter\Plugin;
use lhs\craftpageexporter\models\Asset;
use lhs\craftpageexporter\models\Export;
use lhs\craftpageexporter\models\HtmlAsset;
use lhs\craftpageexporter\models\MiscAsset;

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 */
class Assets extends Component
{
    /** @var Asset[] */
    protected array $_registeredAssets = [];

    /*
     * Register explicitly an asset from its URL and return the export URL of this asset
     * @return string|null
     */
    public function registerAsset($url): ?string
    {
        $asset = $this->createAsset($url);
        $this->_registeredAssets[] = $asset;

        return $asset->getExportUrl();
    }

    /**
     * Return assets registered explicitly with the `registerAsset` method
     * @return Asset[]
     */
    public function getRegisteredAssets(): array
    {
        return $this->_registeredAssets;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRegisteredAssetsSummary(): array
    {
        $summary = [];
        foreach ($this->_registeredAssets as $asset) {
            $summary[] = [
                'exportPath' => $asset->getExportPath(),
                'sourcePath' => $asset->getSourcePath(),
            ];
        }

        return $summary;
    }

    /**
     * Create an asset and apply export transformations
     *
     * @param string $url
     * @return Asset
     */
    protected function createAsset(string $url): Asset
    {
        $baseUrl = Craft::getAlias('@web');
        $export = new Export(Plugin::$plugin->getExportConfig());

        $htmlAsset = new HtmlAsset([
            'export'   => $export,
            'baseUrl'  => $baseUrl,
            'basePath' => $baseUrl,
        ]);

        $asset = new MiscAsset([
            'fromString' => $url,
            'baseUrl'    => $export->baseUrl,
            'basePath'   => $export->baseUrl,
            'export'     => $export,
            'rootAsset'  => $htmlAsset,
        ]);
        $htmlAsset->addChild($asset);
        $export->addRootAsset($htmlAsset);
        $export->transform();


        return $asset;
    }
}
