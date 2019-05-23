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
use craft\errors\SiteNotFoundException;
use craft\web\Controller;
use craft\web\Response;
use craft\web\View;
use lhs\craftpageexporter\Craftpageexporter;
use lhs\craftpageexporter\models\Export;
use lhs\craftpageexporter\models\Settings;
use lhs\craftpageexporter\models\ZipExporter;
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
    protected $allowAnonymous = ['export', 'getExportModalContent'];


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
        $this->requireAdmin();

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
        $this->requireAdmin();

        $export = $this->createExport($entryIds, $siteId);
        $export->printTree();
        die();
    }

    /**
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws SiteNotFoundException
     * @throws BadRequestHttpException
     */
    public function actionGetExportModalContent(): Response
    {
        $this->requireAdmin();
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $entryIds = Craft::$app->getRequest()->getRequiredParam('entryIds');
        $requestId = Craft::$app->getRequest()->getRequiredParam('requestId');
        $siteId = Craft::$app->sites->getCurrentSite()->id;

        // Get modal content
        $view = \Craft::$app->getView();
        $modalHtml = $view->renderTemplate('craft-page-exporter/export-modal', [
            'entryIds' => $entryIds,
            'siteId'   => $siteId,
            'settings' => Craftpageexporter::$plugin->getSettings(),
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
    private function getEntryContent($entry)
    {
        $pageExporterService = Craftpageexporter::$plugin->craftpageexporterService;
        $pageExporterService->setExportContext(true);

        // Get section
        $sectionSiteSettings = $entry->getSection()->getSiteSettings();
        if (!isset($sectionSiteSettings[$entry->siteId]) || !$sectionSiteSettings[$entry->siteId]->hasUrls) {
            throw new ServerErrorHttpException('The entry ' . $entry->id . ' doesn’t have a URL for the site ' . $entry->siteId . '.');
        }

        // Get site
        $site = Craft::$app->getSites()->getSiteById($entry->siteId);
        if (!$site) {
            throw new ServerErrorHttpException('Invalid site ID: ' . $entry->siteId);
        }

        // Set current language
        Craft::$app->language = $site->language;
        Craft::$app->set('locale', Craft::$app->getI18n()->getLocaleById($site->language));

        // Switch to template mode site
        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        return $this->renderTemplate($sectionSiteSettings[$entry->siteId]->template, [
            'entry' => $entry,
        ]);
    }


    /**
     * @param $entryId
     * @param $siteId
     * @return Entry
     * @throws NotFoundHttpException
     */
    private function getEntryModel($entryId, $siteId): Entry
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
     */
    protected function createExport($entryIds, $siteId): Export
    {
        /** @var Settings $settings */
        $settings = Craftpageexporter::$plugin->getSettings();

        // Get params from POST if disponible
        $post = Craft::$app->request->getBodyParams();
        if (!empty($post)) {
            $entryIds = $post['entryIds'];
            $siteId = $post['siteId'];
            $post['flatten'] = !!$post['flatten'];
            $post['inlineScripts'] = !!$post['inlineScripts'];
            $post['inlineStyles'] = !!$post['inlineStyles'];
        }

        // Split IDs
        $ids = explode(',', $entryIds);

        // Create export
        $export = new Export(Craftpageexporter::$plugin->getExportConfig($post));

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
