<?php

namespace lhs\craftpageexporter\variables;

use craft\helpers\Template;
use lhs\craftpageexporter\Plugin;
use nystudio107\pluginvite\variables\ViteVariableInterface;
use nystudio107\pluginvite\variables\ViteVariableTrait;

class PageExporterVariable implements ViteVariableInterface
{
    use ViteVariableTrait;
    /**
     * Register explicitly an asset from its url
     * and return the export URL of this asset
     * @param $url
     * @return string|null
     */
    public function registerAsset($url)
    {
        if (!$url) {
            return '';
        }

        if (!Plugin::$plugin->context->isInExportContext()) {
            return $url;
        }

        return Plugin::$plugin->assets->registerAsset($url);
    }

    /**
     * @return \Twig\Markup
     * @throws \Exception
     */
    public function getRegisteredAssetsSummary()
    {
        $json = json_encode(Plugin::$plugin->assets->getRegisteredAssetsSummary());

        return Template::raw(
            '<page-exporter-registered-assets>' . $json . '</page-exporter-registered-assets>'
        );
    }

    /**
     * @return bool
     */
    public function isInExportContext()
    {
        return Plugin::$plugin->context->isInExportContext();
    }
}
