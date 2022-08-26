<?php

namespace lhs\craftpageexporter\models;

use Exception;

class ImageAsset extends Asset
{
    /** @var string[] All URL found in fromString */
    protected array $urlMatches;

    public function init(): void
    {
        $this->urlMatches = $this->computeUrlMatches();

        if ($this->isFinalImage()) {
            // In this case the extracted string (from initiator), is the URL of this asset
            $this->url = $this->fromString;
        }

        parent::init();
    }

    public function populateChildren(): void
    {
        if ($this->isFinalImage()) {
            return;
        }

        // Multiple images: split into multiple ImageAsset
        foreach ($this->urlMatches as $match) {
            $asset = new ImageAsset([
                'fromString'    => $match,
                'extractFilter' => $this->extractFilter,
                'initiator'     => $this,
                'rootAsset'     => $this->getRootAsset(),
            ]);
            $this->addChild($asset);
            $asset->populateChildren();
        }
    }

    public function getAbsoluteUrl(): ?string
    {
        if ($this->isMultipleImages()) {
            return $this->initiator->getAbsoluteUrl();
        }

        return parent::getAbsoluteUrl();
    }

    /**
     * @return bool|string|null
     * @throws Exception
     */
    public function retrieveContent(): bool|null|string
    {
        // There is no content for this asset because it's composed of multiple images
        if ($this->isMultipleImages()) {
            return null;
        }

        return parent::retrieveContent();
    }


    /**
     * Do not export this asset if it's composed of multiple images
     * @return ?string
     */
    public function getExportPath(): ?string
    {
        if ($this->isMultipleImages()) {
            return null;
        }

        return parent::getExportPath();
    }

    /**
     * Replace URL
     */
    public function updateInitiatorContent(): void
    {
        parent::updateInitiatorContent();
        $this->replaceUrlWithExportUrlInInitiator();
    }

    /**
     * @param string $search
     * @param string $replace
     * @param Asset|null $asset
     */
    public function replaceInContent(string $search, string $replace, Asset $asset = null): void
    {
        // Replace in initiator content
        $this->initiator?->replaceInContent($search, $replace, $asset);
    }

    /**
     * Return all URL found in fromString
     */
    protected function computeUrlMatches(): array
    {
        preg_match_all('#(\S+\.\S+)#', $this->fromString, $matches);

        return $matches[0];
    }

    /**
     * Return true if only one image is found in fromString
     * Return false if this asset is composed of multiple images (child asset)
     * @return bool
     */
    protected function isFinalImage(): bool
    {
        return count($this->urlMatches) === 1;
    }

    /**
     * Return true this asset is composed of multiple images (child asset)
     * Return false if only one image is found in fromString
     * @return bool
     */
    protected function isMultipleImages(): bool
    {
        return !$this->isFinalImage();
    }
}
