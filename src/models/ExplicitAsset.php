<?php

namespace lhs\craftpageexporter\models;


class ExplicitAsset extends Asset
{
    /** @var string Save the original export path */
    protected $_initialExportPath;

    public $sourcePath;

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->_initialExportPath = $this->exportPath;
        $this->url = $this->exportPath;


        parent::init();
        $this->exportPath = $this->_initialExportPath;
    }

    /**
     * Override
     * @return string
     */
    public function getExportPath()
    {
        return $this->_initialExportPath;
    }

    /**
     * Override
     * @return string
     */
    public function getExportUrl()
    {
        return $this->_initialExportPath;
    }

    public function getSourcePath()
    {
        return $this->sourcePath;
    }


    /**
     * Override
     * @return bool|null
     */
    public function isInBaseUrl()
    {
        return true;
    }
}