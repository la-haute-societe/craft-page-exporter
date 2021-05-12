<?php
/**
 * craft-page-exporter plugin for Craft CMS 3.x
 *
 * Craft page exporter
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2019 La Haute Société
 */

namespace lhs\craftpageexporter\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use GuzzleHttp\Client;
use lhs\craftpageexporter\models\Export;
use lhs\craftpageexporter\models\Settings;
use lhs\craftpageexporter\models\ZipExporter;
use lhs\craftpageexporter\Plugin;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    // protected $allowAnonymous = ['export', 'getExportModalContent'];


    // Public Methods
    // =========================================================================

    /**
     * Export entries from IDs and siteId and produce a ZIP archive
     * @param string  $entryIds
     * @param integer $siteId
     * @throws \yii\base\ExitException
     * @throws \Exception
     */
    public function actionExport($entryIds = null, $siteId = null)
    {
        $this->requirePermission('pageExporter.export');

        $export = $this->createExport($entryIds, $siteId);

        // Export to zip
        $exporter = new ZipExporter(['export' => $export]);
        $exporter->export();

        Craft::$app->end();
    }

    /**
     * Export entries from IDs and siteId and produce a ZIP archive
     * @param string  $entryIds
     * @param integer $siteId
     * @throws \Exception
     */
    public function actionAnalyze($entryIds = null, $siteId = null)
    {
        $this->requirePermission('pageExporter.export');

        $export = $this->createExport($entryIds, $siteId);
        $export->printTree();
        die();
    }

    /**
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGetExportModalContent()
    {
        $this->requirePermission('pageExporter.export');
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $entryIds = Craft::$app->getRequest()->getRequiredParam('entryIds');
        $requestId = Craft::$app->getRequest()->getRequiredParam('requestId');
        $siteId = Craft::$app->getRequest()->getRequiredParam('siteId');

        // Get modal content
        $view = \Craft::$app->getView();
        $modalHtml = $view->renderTemplate('craft-page-exporter/export-modal', [
            'entryIds' => $entryIds,
            'siteId'   => $siteId,
            'settings' => Plugin::$plugin->getSettings(),
        ]);

        // Set response to return
        $responseData = [
            'success'   => true,
            'modalHtml' => $modalHtml,
            'requestId' => $requestId,
        ];

        return $this->asJson($responseData);
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
    protected function getEntryModel($entryId, $siteId): Entry
    {
        $entry = Craft::$app->getEntries()->getEntryById($entryId, $siteId);

        if (!$entry) {
            throw new NotFoundHttpException('Entry not found');
        }

        return $entry;
    }

    /**
     * @param $entryIds
     * @param $siteId
     * @return Export
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     * @FIXME: Move this to its own service
     */
    protected function createExport($entryIds, $siteId): Export
    {
        /** @var Settings $settings */
        $settings = Plugin::$plugin->getSettings();

        // Get params from POST if available
        $post = Craft::$app->request->getBodyParams();
        if (!empty($post)) {
            $entryIds = $post['entryIds'];
            $siteId = $post['siteId'];
            $post['inlineScripts'] = (bool)$post['inlineScripts'];
            $post['inlineStyles'] = (bool)$post['inlineStyles'];
        }

        // Split IDs
        $ids = explode(',', $entryIds);

        // Create export
        $export = new Export(Plugin::$plugin->getExportConfig($post));

        // Add each entry to export
        foreach ($ids as $id) {
            $entry = $this->getEntryModel($id, $siteId);

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
}
