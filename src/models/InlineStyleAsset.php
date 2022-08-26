<?php

namespace lhs\craftpageexporter\models;


class InlineStyleAsset extends Asset
{
    public function populateChildren(): void
    {
        $this->populateImages();
    }

    public function retrieveContent(): bool|string|null
    {
        return $this->fromString;
    }

    /**
     * Look for images in this asset content
     */
    protected function populateImages(): void
    {
        preg_match_all('/url\([\'"]?(.*?)[\'"]?\)/', $this->fromString, $matches);
        foreach ($matches[1] as $match) {
            $asset = new MiscAsset([
                'fromString'    => $match,
                'extractFilter' => '/url\([\'"]?(.*?)[\'"]?\)/',
                'initiator'     => $this,
                'rootAsset'     => $this->getRootAsset(),
            ]);
            $this->addChild($asset);
            $asset->populateChildren();
        }
    }

    public function getAbsoluteUrl(): ?string
    {
        return $this->initiator->getAbsoluteUrl();
    }

    /**
     * Inline assets don't need to be exported
     */
    public function getExportPath(): ?string
    {
        return null;
    }
}
