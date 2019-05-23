<?php
/**
 * craft-page-exporter plugin for Craft CMS 3.x
 *
 * Craft page exporter
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2019 La Haute Société
 */

use craft\helpers\UrlHelper;

/**
 * craft-page-exporter config.php
 *
 * This file exists only as a template for the craft-page-exporter settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'craft-page-exporter.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

<?php
/**
 * craft-page-exporter plugin for Craft CMS 3.x
 *
 * Craft page exporter
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2019 La Haute Société
 */

use craft\helpers\UrlHelper;
use lhs\craftpageexporter\models\MiscAsset;
use lhs\craftpageexporter\models\transformers\FlattenTransformer;
use lhs\craftpageexporter\models\Asset;
use lhs\craftpageexporter\models\HtmlAsset;
use lhs\craftpageexporter\models\ImageAsset;
use lhs\craftpageexporter\models\transformers\PrefixExportUrlTransformer;

/**
 * craft-page-exporter config.php
 *
 * This file exists only as a template for the craft-page-exporter settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'craft-page-exporter.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    // 'inlineStyles'      => true,
    // 'inlineScripts'     => true,
    // 'flatten'           => true,
    // 'prefixExportUrl'   => null,
    // 'customSelectors' => [
    //     [
    //         'selectors'  => [
    //             '//video/@poster',
    //         ],
    //         'assetClass' => MiscAsset::class,
    //     ],
    // ],
    // 'assetTransformers' => [
    //     /**
    //      * Remove body and html tags
    //      */
    //     function (Asset $asset) {
    //         if (!($asset instanceof HtmlAsset)) {
    //             return;
    //         }
    //         $content = $asset->getContent();
    //         $content = preg_replace('#<head(.*?)>(.*?)</head>#is', '$2', $content);
    //         $content = preg_replace('#<body(.*?)>(.*?)</body>#is', '$2', $content);
    //         $asset->setContent($content);
    //     },
    // ],
];
