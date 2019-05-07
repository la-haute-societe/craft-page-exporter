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
use craft\records\Element;
use craft\web\Controller;
use craft\web\Response;
use craft\web\View;
use lhs\craftpageexporter\Craftpageexporter;
use lhs\craftpageexporter\models\Export;
use lhs\craftpageexporter\models\ZipExporter;
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
    protected $allowAnonymous = ['export', 'test'];

    // Public Methods
    // =========================================================================

    public function actionTest()
    {
        var_dump('test');
        die();
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionExport()
    {
        $this->requirePostRequest();
        $this->requireAdmin();
        $post = Craft::$app->request->getBodyParams();

        $options = [
            'inlineStyles' => $post['inlineStyles'],
            'inlineScripts' => $post['inlineScripts'],
            'transformers' => []
        ];

        if ($post['flattenTransformer'] === true) {
            $options['transformers'][] = ['type' => 'flatten'];
        }

        if (!empty($post['prefixTransformer'])) {
            $options['transformers'][] = ['type' => 'flatten'];
        }

        $ids = explode(',', $post['entryIds']);

        // Create export
        $export = new Export(Craftpageexporter::$plugin->getExportConfig($options));

        foreach ($ids as $id) {
            // Get entry to export
            $entry = $this->getEntryModel($id, $post['siteId']);
            $entryContent = $this->getEntryContent($entry);

            // Assign a name to the page
            $pageName = sprintf('%s-%s', $entry->slug, Craft::$app->get('locale'));

            // Add page
            $pageUrl = $entry->url;
            $export->addPage($pageName, $pageUrl, $entryContent->data);
        }

        // Transform according to config
        $export->transform();

        // Export to zip
        $exporter = new ZipExporter(['export' => $export]);
        $exporter->export();

        die('ok');
    }

    public function actionGetExportModalContent(): Response
    {
        $this->requireAdmin();
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $entryIds = Craft::$app->getRequest()->getRequiredParam('entryIds');
        $requestId = Craft::$app->getRequest()->getRequiredParam('requestId');
        $siteId = Craft::$app->sites->getCurrentSite()->id;

        $entries = Element::findAll($entryIds);
        if (count($entries) !== count($entryIds)) {
            throw new \Exception('Could not find all elements requested');
        }

        $view = \Craft::$app->getView();
        $modalHtml = $view->renderTemplate('craft-page-exporter/export-modal', [
            'entryIds' => $entryIds,
            'siteId' => $siteId,
            'options' => Craftpageexporter::$plugin->getSettings()
        ]);
        $responseData = [
            'success' => true,
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
}
