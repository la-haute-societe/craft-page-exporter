<?php

namespace lhs\craftpageexporter\models;


class ImageAsset extends Asset
{

    /** @var string[] All URL found in fromString */
    protected $urlMatches;

    /**
     * Init
     */
    public function init()
    {
        $this->urlMatches = $this->computeUrlMatches();

        if ($this->isFinalImage()) {
            // In this case the extracted string (from initiator)
            // is the URL of this asset
            $this->url = $this->fromString;
        }

        parent::init();
    }

    /**
     * @return null|void
     */
    public function populateChildren()
    {
        if ($this->isFinalImage()) {
            return;
        }

        // Multiple image: split into multiple ImageAsset
        foreach ($this->urlMatches as $match) {
            $asset = new ImageAsset([
                'fromString'    => $match,
                'extractFilter' => $this->extractFilter,
                'initiator'     => $this,
            ]);
            $this->addChild($asset);
            $asset->populateChildren();
        }
    }

    /**
     * @return null|string
     */
    public function getAbsoluteUrl()
    {
        if ($this->isMultipleImages()) {
            return $this->initiator->getAbsoluteUrl();
        }

        return parent::getAbsoluteUrl();
    }

    /**
     * @return null|string
     * @throws \Exception
     */
    public function retrieveContent()
    {
        // There is no content for this asset
        // because it's composed of multiple images
        if ($this->isMultipleImages()) {
            return null;
        }

        return parent::retrieveContent();
    }


    /**
     * Do not export this asset if it's composed
     * of multiple images
     * @return mixed|null
     */
    public function getExportPath()
    {
        if ($this->isMultipleImages()) {
            return null;
        }

        return parent::getExportPath();
    }


    /**
     * Replace URL
     */
    public function updateInitiatorContent()
    {
        parent::updateInitiatorContent();

        if ($this->isInBaseUrl() && $this->url && $this->initiator) {
            $this->initiator->replaceInContent($this->url, $this->getExportUrl(), $this);
        }
    }

    /**
     * @param string     $search
     * @param string     $replace
     * @param Asset|null $asset
     */
    public function replaceInContent($search, $replace, $asset = null)
    {
        // Replace in initiator content
        if ($this->initiator) {
            $this->initiator->replaceInContent($search, $replace, $asset);
        }
    }

    /**
     * Return all URL found in fromString
     * @return mixed
     */
    protected function computeUrlMatches()
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
    protected function isMultipleImages()
    {
        return !$this->isFinalImage();
    }
}