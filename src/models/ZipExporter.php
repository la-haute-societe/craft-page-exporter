<?php

namespace lhs\craftpageexporter\models;

use Craft;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use lhs\craftpageexporter\Plugin;
use yii\base\ExitException;
use ZipArchive;

/**
 * Class ZipExporter
 * @package lhs\craftpageexporter\models
 */
class ZipExporter extends BaseExporter
{
    protected ZipArchive $archive;
    protected string $tempFilename;
    public string $archiveName = 'export';

    /**
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        // Create empty archive
        $this->archive = new ZipArchive();
        $this->tempFilename = Craft::$app->path->getTempPath() . '/export-' . date('Y-m-d-H-i-s') . '.zip';
        if ($this->archive->open($this->tempFilename, ZipArchive::CREATE) !== true) {
            throw new Exception("Cannot create {$this->tempFilename}\n");
        }
    }

    /**
     * Export
     * @throws Exception
     */
    public function export(): void
    {
        foreach ($this->export->getRootAssets() as $rootAsset) {
            if (!($rootAsset instanceof HtmlAsset)) {
                throw new Exception('Root asset mut be an HtmlAsset.');
            }

            $this->addRootAsset($rootAsset);
        }

        foreach (Plugin::$plugin->assets->getRegisteredAssets() as $asset) {
            $this->addAsset($asset);
        }

        $this->sendZip();
    }


    /**
     * @return void
     * @throws ExitException
     */
    public function sendZip(): void
    {
        $this->archive->close();
        Craft::$app->end(
            0,
            Craft::$app->getResponse()->sendFile($this->tempFilename, $this->archiveName . '.zip')
        );
    }

    /**
     * @param HtmlAsset $rootAsset
     */
    protected function addRootAsset(HtmlAsset $rootAsset): void
    {
        $this->addFile($rootAsset->name, $rootAsset->getContent());

        foreach ($rootAsset->children as $child) {
            $this->addAsset($child);
        }
    }

    /**
     * @param Asset $asset
     */
    protected function addAsset(Asset $asset): void
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
    protected function addFile(string $name, string $content): void
    {
        $this->archive->addFromString($name, $content);
    }
}
