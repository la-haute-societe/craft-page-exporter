<?php

namespace lhs\craftpageexporter\variables;

use craft\helpers\Template;
use Exception;
use lhs\craftpageexporter\Plugin;
use nystudio107\pluginvite\variables\ViteVariableInterface;
use nystudio107\pluginvite\variables\ViteVariableTrait;
use Twig\Markup;

class PageExporterVariable implements ViteVariableInterface
{
    use ViteVariableTrait;

    /**
     * Register explicitly an asset from its URL and return the export URL of this asset
     * @param $url
     * @return ?string|null
     */
    public function registerAsset($url): ?string
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
     * @return Markup
     * @throws Exception
     */
    public function getRegisteredAssetsSummary(): Markup
    {
        $json = json_encode(Plugin::$plugin->assets->getRegisteredAssetsSummary(), JSON_THROW_ON_ERROR);

        return Template::raw(
            '<page-exporter-registered-assets>' . $json . '</page-exporter-registered-assets>'
        );
    }

    public function isInExportContext(): bool
    {
        return Plugin::$plugin->context->isInExportContext();
    }
}
