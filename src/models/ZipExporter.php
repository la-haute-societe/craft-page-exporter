<?php

namespace lhs\craftpageexporter\models;

use Craft;
use Yii;
use ZipArchive;

/**
 * Class ZipExporter
 * @package lhs\craftpageexporter\models
 */
class ZipExporter extends BaseExporter
{
    /** @var \ZipArchive */
    protected $archive;

    /** @var string */
    protected $tempFilename;

    /** @var string */
    protected $archiveName = 'export';

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        // Create empty archive
        $this->archive = new ZipArchive();
        $this->tempFilename = Craft::$app->path->getTempPath() . '/export-' . date('Y-m-d-H-i-s') . '.zip';
        if ($this->archive->open($this->tempFilename, ZipArchive::CREATE) !== true) {
            throw new \Exception("Cannot create {$this->tempFilename}\n");
        }
    }

    /**
     * Export
     * @throws \Exception
     */
    public function export()
    {
        foreach ($this->export->getRootAssets() as $rootAsset) {
            if (!($rootAsset instanceof HtmlAsset)) {
                throw new \Exception('Root asset mut be an HtmlAsset.');
            }

            $this->addRootAsset($rootAsset);
        }

        $this->sendZip();
    }


    /**
     * @return void
     */
    public function sendZip()
    {
        $this->archive->close();
        Yii::$app->response->sendFile($this->tempFilename, $this->archiveName . '.zip')->send();
        Yii::$app->end();
    }

    /**
     * @param HtmlAsset $rootAsset
     */
    protected function addRootAsset(HtmlAsset $rootAsset)
    {
        $this->addFile($rootAsset->name, $rootAsset->getContent());

        foreach ($rootAsset->children as $child) {
            $this->addAsset($child);
        }
    }

    /**
     * @param Asset $asset
     */
    protected function addAsset(Asset $asset)
    {
        if ($asset->willBeInArchive) {
            $name = $asset->getExportPath();

            if ($name) {
                $this->addFile($name, $asset->getContent());
            }
        }

        foreach ($asset->children as $child) {
            $this->addAsset($child);
        }
    }

    /**
     * @param string $name
     * @param string $content
     */
    protected function addFile($name, $content)
    {
        $this->archive->addFromString($name, $content);
    }
}