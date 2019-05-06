<?php

namespace lhs\craftpageexporter\models;

use DOMElement;
use Symfony\Component\DomCrawler\Crawler;

class HtmlAsset extends Asset
{

    /** @var string */
    public $name;

    /** @var array */
    public $selectorTypes = [
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

    /** @var array */
    public $sourceAttributes = [
        'src',
        'href',
        'poster',
    ];

    /** @var Crawler $crawler */
    protected $crawler;

    /**
     *
     * @throws \Exception
     */
    public function populateChildren()
    {
        $this->crawler = new Crawler($this->fromString);

        // Add children
        foreach ($this->selectorTypes as $selectorType) {
            $assetClass = $selectorType['assetClass'];
            $selectors = $selectorType['selectors'];

            foreach ($selectors as $selector) {
                $this->crawler->evaluate($selector)->each(function (Crawler $crawler) use ($assetClass, $selector) {
                    $this->addChildrenFromDomElement($crawler, $assetClass, $selector);
                });
            }
        }

        // Populate chilren
        foreach ($this->children as $child) {
            $child->populateChildren();
        }
    }

    /**
     * Override default retrieveContent
     * @return bool|string
     */
    public function retrieveContent()
    {
        return $this->fromString;
    }


    /**
     * @param Crawler $crawler
     * @param         $assetClass
     * @param         $filter
     */
    protected function addChildrenFromDomElement($crawler, $assetClass, $filter)
    {
        $nodeText = $crawler->text();

        /** @var Asset $asset */
        $asset = new $assetClass([
            'fromString'     => $nodeText,
            'fromDomElement' => $crawler->getNode(0),
            'extractFilter'  => $filter,
            'initiator'      => $this,
        ]);
        $this->addChild($asset);
    }

    /**
     * Replace $domElement with $replace in this asset content
     * @param DOMElement $domElement
     * @param DOMElement $replaceElement
     * @return null|void
     */
    public function replaceDomElement($domElement, $replaceElement)
    {
        // If domElement is an attribute, get the element (parent) instead
        if ($domElement->nodeType === XML_ATTRIBUTE_NODE) {
            $domElement = $domElement->parentNode;
        }

        $domElement->parentNode->replaceChild($replaceElement, $domElement);
    }

    /**
     * Remove $domElement
     * @param DOMElement $domElement
     * @return null|void
     */
    public function removeDomElement($domElement)
    {
        // If domElement is an attribute, get the element (parent) instead
        if ($domElement->nodeType === XML_ATTRIBUTE_NODE) {
            $domElement = $domElement->parentNode;
        }

        $domElement->parentNode->removeChild($domElement);
    }

    /**
     * Return content from DomElement
     * @return string
     */
    public function getContent()
    {
        return $this->crawler->html();
    }


    /**
     * Replace $search by $replace in the DOM
     * @param string     $search
     * @param string     $replace
     * @param Asset|null $asset
     */
    public function replaceInContent($search, $replace, $asset = null)
    {
        $asset->fromDomElement->nodeValue = str_replace($search, $replace, $asset->fromDomElement->nodeValue);
    }
}