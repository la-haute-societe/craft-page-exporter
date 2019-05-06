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

use craft\base\Component;
use lhs\craftpageexporter\Craftpageexporter;
use lhs\craftpageexporter\models\Asset;
use lhs\craftpageexporter\models\Export;
use lhs\craftpageexporter\models\MiscAsset;

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 * @since     1.0.0
 */
class CraftpageexporterService extends Component
{
    /** @var Asset[] */
    protected $_registeredAssets = [];
    protected $_exportContext;

    /*
     * Register explicitly an asset from its url
     * and return the export URL of this asset
     * @return string|null
     */
    public function registerAsset($url)
    {
        $asset = $this->createAsset($url);

        $this->_registeredAssets[] = $asset;

        return $asset->getExportUrl();
    }

    /**
     * Return assets registered explicitly with the `registerAsset` method
     * @return Asset[]
     */
    public function getRegisteredAssets()
    {
        return $this->_registeredAssets;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRegisteredAssetsSummary()
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
     * @param string $url
     * @return Asset
     */
    protected function createAsset($url)
    {
        $export = new Export(Craftpageexporter::$plugin->getExportConfig());
        $asset = new MiscAsset([
            'fromString' => $url,
            'baseUrl'    => $export->baseUrl,
            'basePath'   => $export->baseUrl,
            'export'     => $export,
        ]);
        $export->addRootAsset($asset);
        $export->transform();

        return $asset;
    }

    /**
     * Return true if the current request is an export
     * From Get param or explicitly defined with setExportContext method
     * @return bool
     */
    public function isInExportContext()
    {
        $fromGetParam = (int)\Craft::$app->request->getParam('pageExporterContext') === 1;

        return $this->_exportContext || $fromGetParam;
    }

    /**
     * Set export context
     * @param bool $status
     */
    public function setExportContext($status = true)
    {
        $this->_exportContext = $status;
    }


}
