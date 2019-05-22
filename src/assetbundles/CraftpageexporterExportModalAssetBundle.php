<?php
/**
 * craft-page-exporter plugin for Craft CMS 3.x
 *
 * Craft page exporter
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2019 La Haute Société
 */

namespace lhs\craftpageexporter\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 * @since     1.0.0
 */
class CraftpageexporterExportModalAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $assetsFilenames = json_decode(file_get_contents(__DIR__ . "/../resources/webpack-assets.json"), true);

        // define the path that your publishable resources live
        $this->sourcePath = '@lhs/craftpageexporter/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            $assetsFilenames['CraftpageexporterExportModal']['js'],
        ];

        $this->css = [
            $assetsFilenames['craftpageexporter']['css'],
        ];

        parent::init();
    }
}
