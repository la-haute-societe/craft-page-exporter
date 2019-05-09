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
use lhs\craftpageexporter\models\transformers\FlattenTransformer;
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
    'baseUrl'       => UrlHelper::baseRequestUrl(),
    'inlineStyles'  => true,
    'inlineScripts' => true,
    'transformers'  => [
        ['type' => 'flatten'],
//        ['type' => 'prefix', 'prefix' => sprintf('https://cdn.test.com/%s', date('Y-m'))],
    ],
];