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
        $fromGetParam = (int)\Craft::$app->request->getParam('pageExporterContext') === 1;

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
