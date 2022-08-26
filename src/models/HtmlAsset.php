<?php

namespace lhs\craftpageexporter\models;

use DOMNode;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class HtmlAsset extends Asset
{
    public string $name;

    /**
     * @see Settings::$customSelectors
     * @var string[]
     */
    public array $customSelectors = [];

    /**
     * @see Settings::$customSelectors
     * @var array
     */
    protected array $selectorTypes = [
        [
            'selectors'  => [
                '//page-exporter-registered-assets',
            ],
            'assetClass' => ExplicitTagAsset::class,
        ],
        [
            'selectors'  => [
                '//script/@src',
            ],
            'assetClass' => ScriptAsset::class,
        ],
        [
            'selectors'  => [
                '//style',
                '//*/@style',
            ],
            'assetClass' => InlineStyleAsset::class,
        ],
        [
            'selectors'  => [
                '//link[@type="text/css"]/@href',
                '//link[@rel="stylesheet"]/@href',
            ],
            'assetClass' => StyleAsset::class,
        ],
        [
            'selectors'  => [
                '//img/@src',
                '//img/@srcset',
                '//picture//source/@srcset',
                '//*/@data-bgset',
                '//*/@data-srcset',
                '//*/@data-src',
                '//link[contains(@rel, "icon")]/@href',
                '//link[@rel="manifest"]/@href',
                '//meta[contains(@property, "image")]/@content',
            ],
            'assetClass' => ImageAsset::class,
        ],
        [
            'selectors'  => [
                '//video/@poster',
                '//video/@src',
                '//video//source/@src',
                '//audio//source/@src',
                '//object/@data',
                '//object/@src',
                '//object//param/@value',
            ],
            'assetClass' => MiscAsset::class,
        ],
    ];

    /** @var Crawler $crawler */
    protected Crawler $crawler;

    /**
     *
     * @throws Exception
     */
    public function populateChildren(): void
    {
        $this->crawler = new Crawler();
        $this->crawler->addHtmlContent($this->fromString);

        // Default selectors + custom selectors
        $selectorTypes = array_merge($this->selectorTypes, $this->customSelectors);

        // Add children
        foreach ($selectorTypes as $selectorType) {
            $assetClass = $selectorType['assetClass'];
            $selectors = $selectorType['selectors'];

            foreach ($selectors as $selector) {
                $this->crawler->evaluate($selector)->each(function (Crawler $crawler) use ($assetClass, $selector) {
                    $this->addChildrenFromDomElement($crawler, $assetClass, $selector);
                });
            }
        }

        // Populate children
        foreach ($this->children as $child) {
            $child->populateChildren();
        }
    }

    /**
     * Override default retrieveContent
     * @return bool|string|null
     */
    public function retrieveContent(): bool|null|string
    {
        return $this->fromString;
    }

    /**
     * @param Crawler $crawler
     * @param string $assetClass
     * @param ?string $filter
     */
    protected function addChildrenFromDomElement(Crawler $crawler, string $assetClass, ?string $filter): void
    {
        $this->addChild(new $assetClass([
            'fromString'     => $crawler->text(),
            'fromDomElement' => $crawler->getNode(0),
            'extractFilter'  => $filter,
            'initiator'      => $this,
            'rootAsset'      => $this,
        ]));
    }

    /**
     * Replace $domElement with $replace in this asset content
     *
     * @param DOMNode $domElement
     * @param DOMNode $replace
     * @return void
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public function replaceDomElement(DOMNode $domElement, DOMNode $replace): void
    {
        // If domElement is an attribute, get the element (parent) instead
        if ($domElement->nodeType === XML_ATTRIBUTE_NODE) {
            $domElement = $domElement->parentNode;
        }

        $domElement->parentNode->replaceChild($replace, $domElement);
        $this->updateContentFromDomCrawler();
    }

    /**
     * Remove $domElement
     * @param DOMNode $domElement
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public function removeDomElement(DOMNode $domElement): void
    {
        // If domElement is an attribute, get the element (parent) instead
        if ($domElement->nodeType === XML_ATTRIBUTE_NODE) {
            $domElement = $domElement->parentNode;
        }

        $domElement->parentNode->removeChild($domElement);
        $this->updateContentFromDomCrawler();
    }

    /**
     * Replace $search by $replace in the DOM
     * @param string $search
     * @param string $replace
     * @param ?Asset $asset
     */
    public function replaceInContent(string $search, string $replace, Asset $asset = null): void
    {
        if (!$asset) {
            return;
        }

        $asset->fromDomElement->nodeValue = htmlentities(str_replace($search, $replace, $asset->fromDomElement->nodeValue));
        $this->updateContentFromDomCrawler();
    }

    /**
     * Update content with HTML extracted from DOM tree
     */
    protected function updateContentFromDomCrawler(): void
    {
        // Use "saveDocument" instead of "html", otherwise the 'html' tag is not exported
        $html = '';
        foreach ($this->crawler as $domElement) {
            $html.= $domElement->ownerDocument->saveHTML();
        }

        $this->setContent($html);
    }
}
