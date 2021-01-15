<?php

namespace lhs\craftpageexporter\variables;


use craft\helpers\Template;


class PageExporterVariable
{
    // Public Methods
    // =========================================================================


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

        $pageExporterService = Craftpageexporter::$plugin->craftpageexporterService;

        if (!$pageExporterService->isInExportContext()) {
            return $url;
        }

        return $pageExporterService->registerAsset($url);
    }

    /**
     * @return \Twig\Markup
     * @throws \Exception
     */
    public function getRegisteredAssetsSummary()
    {
        $pageExporterService = Craftpageexporter::$plugin->craftpageexporterService;
        $json = json_encode($pageExporterService->getRegisteredAssetsSummary());

        return Template::raw(
            '<page-exporter-registered-assets>' . $json . '</page-exporter-registered-assets>'
        );
    }

    /**
     * @return bool
     */
    public function isInExportContext()
    {
        $pageExporterService = Craftpageexporter::$plugin->craftpageexporterService;

        return $pageExporterService->isInExportContext();
    }
}
