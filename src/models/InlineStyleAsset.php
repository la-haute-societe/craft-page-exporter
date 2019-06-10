<?php

namespace lhs\craftpageexporter\models;


class InlineStyleAsset extends Asset
{

    /**
     * @return null|void
     */
    public function populateChildren()
    {
        $this->populateImages();
    }

    /**
     * @return bool|string
     */
    public function retrieveContent()
    {
        return $this->fromString;
    }

    /**
     * Look for images in this asset content
     */
    protected function populateImages()
    {
        preg_match_all('/url\([\'"]?(.*?)[\'"]?\)/', $this->fromString, $matches);
        foreach ($matches[1] as $match) {
            $asset = new ImageAsset([
                'fromString'    => $match,
                'extractFilter' => '/url\([\'"]?(.*?)[\'"]?\)/',
                'initiator'     => $this,
                'rootAsset'     => $this->getRootAsset(),
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
        return $this->initiator->getAbsoluteUrl();
    }

    /**
     * Do not export this asset
     * @return mixed|null
     */
    public function getExportPath()
    {
        return null;
    }

}