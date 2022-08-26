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
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use lhs\craftpageexporter\models\Settings;
use lhs\craftpageexporter\Plugin;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use lhs\craftpageexporter\models\Export as ExportModel;

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 */
class Export extends Component
{
    /**
     * @param array $entryIds
     * @param int $siteId
     * @param array $config
     * @return ExportModel
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function createExport(array $entryIds, int $siteId, array $config = []): ExportModel
    {
        $settings = Plugin::$plugin->getSettings();

        // Create export
        $export = new ExportModel(Plugin::$plugin->getExportConfig($config));

        // Add each entry to export
        foreach ($entryIds as $id) {
            $entry = $this->getEntryModel((int)$id, $siteId);

            if (is_callable($settings->entryContentExtractor)) {
                $entryContent = ($settings->entryContentExtractor)($entry);
            } else {
                $renderedEntry = $this->getEntryContent($entry);
                $entryContent = $renderedEntry->data;
            }

            // Assign a name to the page
            $pageName = sprintf('%s-%s', $entry->slug, strtoupper(Craft::$app->sites->getSiteById($siteId)->language));

            // Add page
            $pageUrl = $entry->url;
            $export->addPage($pageName, $pageUrl, $entryContent);
        }

        // Transform according to config
        $export->transform();

        return $export;
    }

    /**
     * @param Entry $entry
     * @return object
     * @throws GuzzleException
     */
    protected function getEntryContent(Entry $entry): object
    {
        $client = Craft::createGuzzleClient();
        $response = $client->get(UrlHelper::urlWithParams($entry->getUrl(), [
            'pageExporterContext' => 1,
        ]));

        $entryContent = $response->getBody()->__toString();
        return (object)['data' => $entryContent];
    }

    /**
     * @param int $entryId
     * @param int $siteId
     * @return Entry
     * @throws NotFoundHttpException
     */
    protected function getEntryModel(int $entryId, int $siteId): Entry
    {
        $entry = Craft::$app->getEntries()->getEntryById($entryId, $siteId);

        if (!$entry) {
            throw new NotFoundHttpException('Entry not found');
        }

        return $entry;
    }
}
