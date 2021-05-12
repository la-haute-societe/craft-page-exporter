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
use GuzzleHttp\Client;
use lhs\craftpageexporter\models\Settings;
use lhs\craftpageexporter\Plugin;
use yii\web\NotFoundHttpException;
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
     * @param int   $siteId
     * @param array $config
     * @return ExportModel
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function createExport(array $entryIds, int $siteId, array $config = []): ExportModel
    {
        /** @var Settings $settings */
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
            $pageName = sprintf('%s-%s', $entry->slug, Craft::$app->get('locale'));

            // Add page
            $pageUrl = $entry->url;
            $export->addPage($pageName, $pageUrl, $entryContent, $entry);
        }

        // Transform according to config
        $export->transform();

        return $export;
    }

    /**
     * @param Entry $entry
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \yii\base\Exception
     */
    protected function getEntryContent($entry)
    {
        $client = new Client();
        $response = $client->get($entry->getUrl());

        $entryContent = $response->getBody()->__toString();
        return (object)['data' => $entryContent];
    }

    /**
     * @param $entryId
     * @param $siteId
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
