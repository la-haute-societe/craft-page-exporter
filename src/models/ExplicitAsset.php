<?php

namespace lhs\craftpageexporter\models;


use Exception;

class ExplicitAsset extends Asset
{
    /** @var string Save the original export path */
    protected string $_initialExportPath;

    /** @var string */
    public string $sourcePath;

    /**
     * @throws Exception
     */
    public function init(): void
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
    public function getExportPath(): string
    {
        return $this->_initialExportPath;
    }

    /**
     * Override
     * @return string
     */
    public function getExportUrl(): string
    {
        return $this->_initialExportPath;
    }

    public function getSourcePath(): ?string
    {
        return $this->sourcePath;
    }

    /**
     * Override
     * @return bool|null
     */
    public function isInBaseUrl(): ?bool
    {
        return true;
    }
}
