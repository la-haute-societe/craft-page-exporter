<?php

namespace lhs\craftpageexporter\models;


use Exception;

class StyleAsset extends Asset
{
    /** @var ?Asset Inline asset containing this asset file content */
    public ?Asset $inlineAsset = null;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        // In this case the extracted string (from initiator) is the URL of this asset
        $this->url = $this->fromString;
        parent::init();
    }

    /**
     * Retrieve and update content
     * @throws Exception
     */
    protected function retrieveAndUpdateContent(): void
    {
        // Do nothing
        // Content is downloaded in the populateChildren method
    }

    /**
     * Get content of this asset
     * Proxy to inlineAsset
     */
    public function getContent(): ?string
    {
        return $this->inlineAsset?->getContent();
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function populateChildren(): void
    {
        // We don't want to download files outside the base URL
        if (!$this->isInBaseUrl()) {
            return;
        }

        // URL of its children is relative to this asset (not the root asset)
        $baseUrl = dirname($this->getAbsoluteUrl()) . '/';

        // Do not store content in this asset; use an InlineStyleAsset child instead
        $content = $this->retrieveContent();

        // Create child asset containing content extracted
        // from this asset file, and use it to extract assets
        $this->inlineAsset = new InlineStyleAsset([
            'fromString'    => $content,
            'extractFilter' => $this->extractFilter,
            'initiator'     => $this,
            'baseUrl'       => $baseUrl,
            'basePath'      => $this->basePath,
            'rootAsset'     => $this->getRootAsset(),
        ]);
        $this->addChild($this->inlineAsset);
        $this->inlineAsset->populateChildren();

    }

    /**
     * Replace paths and inline styles if needed
     */
    public function updateInitiatorContent(): void
    {
        if (!$this->willBeInArchive || !$this->url) {
            return;
        }

        // Update paths in child asset content and this content
        parent::updateInitiatorContent();

        // Inline style in Html
        if ($this->export->inlineStyles) {
            $this->inlineStyleInHtmlAsset();
        } else {
            $this->replaceStyleTagHref();
        }
    }

    public function setExportUrl(string $exportUrl): void
    {
        parent::setExportUrl($exportUrl);

        // If we change the URL of this asset, we have to recalculate URL of its children
        if (!$this->export->inlineStyles) {
            foreach ($this->children as $child) {
                $child->setBaseUrl(dirname($this->getAbsoluteUrl()) . '/');
            }
        }
    }

    /**
     * Replace style tag with style content in the HtmlAsset when this asset is inlined.
     */
    protected function inlineStyleInHtmlAsset(): void
    {
        $this->setBaseUrl($this->getRootAsset()->getBaseUrl());

        $document = $this->fromDomElement->ownerDocument;
        $replaceElement = $document->createElement('style', $this->getContent());
        $this->initiator->replaceDomElement($this->fromDomElement, $replaceElement);
        $this->setRecursiveFromDomElement($replaceElement);

        // Attach children asset to initiator asset
        foreach ($this->children as $child) {
            $this->initiator->addChild($child);
        }
        $this->initiator->removeChild($this);
    }

    /**
     * Replace style tag href in HtmlAsset content.
     */
    protected function replaceStyleTagHref(): void
    {
        $this->replaceUrlWithExportUrlInInitiator();
    }
}
