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

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 */
class Context extends Component
{
    protected $_exportContext;

    /**
     * Return true if the current request is an export
     * From Get param or explicitly defined with setExportContext method
     * @return bool
     */
    public function isInExportContext()
    {
         if (Craft::$app->request->isConsoleRequest) { // Handle install from console command/bash script
            $fromGetParam = false;

            $params = Craft::$app->request->getParams();

            if (!empty($params['pageExporterContext'])) {
                $fromGetParam = (int)$params['pageExporterContext'] === 1;
            }

        } else {
            $fromGetParam = (int)Craft::$app->request->getParam('pageExporterContext') === 1;
        }

        return $this->_exportContext || $fromGetParam;
    }

    /**
     * Set export context
     * @param bool $status
     */
    public function setExportContext($status = true)
    {
        $this->_exportContext = $status;
    }
}
