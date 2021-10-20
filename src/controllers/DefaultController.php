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
use craft\web\Controller;
use lhs\craftpageexporter\models\ZipExporter;
use lhs\craftpageexporter\Plugin;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\web\BadRequestHttpException;

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
     * @param $siteId
     * @throws \yii\base\ExitException
     * @throws \Exception
     */
    public function actionExport($entryIds = null, $siteId = null)
    {
        $this->requirePermission('pageExporter.export');

        $request = Craft::$app->request;

        $siteId = $siteId ?? $request->getRequiredParam('siteId');
        $ids = $entryIds ?? $request->getRequiredParam('entryIds');
        if(!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        $settings = Plugin::getInstance()->getSettings();

        $exportModelParams = [
            'inlineScripts' => (bool)$request->getBodyParam('inlineScripts', $settings->inlineScripts),
            'inlineStyles'  => (bool)$request->getBodyParam('inlineStyles', $settings->inlineStyles),
        ];

        $exportUrlFormat = $request->getBodyParam('exportUrlFormat');
        if ($exportUrlFormat !== null) {
            $exportModelParams['exportUrlFormat'] = $exportUrlFormat;
        }
        $exportPathFormat = $request->getBodyParam('exportPathFormat');
        if ($exportPathFormat !== null) {
            $exportModelParams['exportPathFormat'] = $exportPathFormat;
        }

        $export = Plugin::$plugin->export->createExport($ids, (int)$siteId, $exportModelParams);

        // Export to zip
        $exporter = new ZipExporter([
            'export' => $export,
            'archiveName' => (string)$request->getBodyParam('archiveName', $settings->archiveName),
        ]);
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

        $export = Plugin::$plugin->export->createExport($entryIds, $siteId);
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
        $modalHtml = $view->renderTemplate('craft-page-exporter/export-modal',
            [
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
}
