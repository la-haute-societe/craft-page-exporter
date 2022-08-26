<?php

namespace lhs\craftpageexporter\models;


class ScriptAsset extends Asset
{
    public function init(): void
    {
        // In this case the extracted string (from initiator) is the URL of this asset
        $this->url = $this->fromString;
        parent::init();
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
        if ($this->export->inlineScripts) {
            $this->inlineScriptInHtmlAsset();
        } else {
            $this->replaceScriptTagHref();
        }
    }

    /**
     * Replace style tag with style content in the HtmlAsset when this asset is inlined.
     */
    protected function inlineScriptInHtmlAsset(): void
    {
        $document = $this->fromDomElement->ownerDocument;
        $replaceElement = $document->createElement('script');
        $content = $this->getContent();

        $replaceElement->nodeValue = htmlspecialchars($content);
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
    protected function replaceScriptTagHref(): void
    {
        $this->replaceUrlWithExportUrlInInitiator();
    }
}
