<?php

namespace lhs\craftpageexporter\models;

use craft\base\Component;
use craft\helpers\UrlHelper;
use DOMElement;
use lhs\craftpageexporter\helpers\PhpUri;

/**
 * Class Asset
 *
 * Asset lifecycle :
 * 1. Init -> retrieve content
 * 2. Populate children
 * 3. Update initiator content (URL, inline styles...)
 * 4. From exporter: getExportPath
 *
 * @package lhs\craftpageexporter\models
 */
abstract class Asset extends Component
{
    /**
     * BaseURL and BasePath are differents in StyleAsset
     * For images, path is relative to basePath and url is relative to baseURL (url of the stylesheet file)
     */
    /** @var string Base URL: only assets under this path urll be exported, used to calculate relative url */
    public $baseUrl = null;

    /** @var string Path this asset is relative to, used to calculate relative path */
    public $basePath = null;

    /** @var string From string as found in the sources */
    public $fromString = null;

    /** @var DOMElement If the initiator is HtmlAsset, this is the DomNode referencing this asset */
    public $fromDomElement = null;

    /** @var Asset[] Extracted children of this asset */
    public $children = [];

    /** @var Asset Who initiate this asset */
    public $initiator = null;

    /** @var string|null How this asset was extracted */
    public $extractFilter = null;

    /** @var string|null $url URL as found in sources (if this asset has URL) */
    public $url = null;

    /** @var Export The export object to which this asset belongs */
    public $export;

    /** @var string Export path */
    public $exportPath;

    /** @var string Export UrL */
    public $exportUrl;

    /** @var null Content of this asset */
    protected $_content = null;


    /**
     * Asset init
     * Define default configuration and retrieve the content of this asset
     * @inheritdoc
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        if (!$this->export) {
            if (!$this->initiator) {
                throw new \Exception('The export attribute must be defined for the root asset.');
            }
            $this->export = $this->initiator->export;
        }

        // Set baseUrl from initiator if not specified
        if (!$this->baseUrl && $this->initiator) {
            $this->baseUrl = $this->initiator->baseUrl;
        }

        // Set basePath from initiator if not specified
        if (!$this->basePath && $this->initiator) {
            $this->basePath = $this->initiator->basePath;
        }

        // Set fromDomElement from initiator if not specified
        if (!$this->fromDomElement && $this->initiator) {
            $this->fromDomElement = $this->initiator->fromDomElement;
        }

        // Set sourcePathTransformer from initiator if not specified
        if (!$this->export->sourcePathTransformer && $this->initiator) {
            $this->export->sourcePathTransformer = $this->initiator->export->sourcePathTransformer;
        }

        $this->exportUrl = $this->getRelativeUrl();
        $this->exportPath = $this->getRelativePath();

        $this->retrieveAndUpdateContent();

    }

    /**
     * Add a child on this asset
     * @param Asset $child
     */
    public function addChild(Asset $child)
    {
        $this->children[] = $child;
    }

    /**
     * Remove child
     * @param Asset $asset
     */
    public function removeChild(Asset $asset)
    {
        foreach ($this->children as $key => $child) {
            if ($child === $asset) {
                unset($this->children[$key]);
            }
        }
    }

    /**
     * Parse this asset content and create children assets
     * @return null
     */
    public function populateChildren()
    {
        return null;
    }

    /**
     * Return path of this asset in the export
     * @return mixed|null
     */
    public function getExportPath()
    {
        return $this->exportPath;
    }

    /**
     * Return URL of this asset in the export
     * @return string|null
     */
    public function getExportUrl()
    {
        return $this->exportUrl;
    }

    /**
     * Return the path (file or URL) used for getting the content of this asset
     * @return null|string
     * @throws \Exception
     */
    public function getSourcePath()
    {
        // Use overriden transform method
        if (!is_callable($this->export->sourcePathTransformer)) {
            throw new \Exception('sourcePathTransformer function of the export is not callable.');
        }

        return ($this->export->sourcePathTransformer)($this);
    }

    /**
     * Return the absolute URL of this asset
     * Return null if this asset hasn't URL
     * @return null|string
     */
    public function getAbsoluteUrl()
    {
        if (empty($this->url)) {
            return null;
        }

        if (UrlHelper::isAbsoluteUrl($this->url)) {
            return $this->url;
        }

        if (is_null($this->initiator)) {
            return $this->url;
        }

        $parentUrl = $this->initiator->getAbsoluteUrl();
        $absoluteUrl = PhpUri::parse($parentUrl)->join($this->url);

        return $absoluteUrl;
    }

    /**
     * Return path of this asset relative to basePath (the root of this export)
     * Return null if this asset hasn't URL
     * @return mixed|null
     */
    public function getRelativePath()
    {

        if (!$this->isInBaseUrl()) {
            return null;
        }

        $absoluteUrl = $this->getAbsoluteUrl();

        if (is_null($absoluteUrl)) {
            return null;
        }

        return str_replace($this->basePath, '', $absoluteUrl);
    }

    /**
     * Return URL of this asset relative to baseUrl (its initiator)
     * Return null if this asset hasn't URL
     * @return mixed|null
     */
    public function getRelativeUrl()
    {

        if (!$this->isInBaseUrl()) {
            return null;
        }

        $absoluteUrl = $this->getAbsoluteUrl();

        if (is_null($absoluteUrl)) {
            return null;
        }

        return str_replace($this->baseUrl, '', $absoluteUrl);
    }

    /**
     * Return true if the absolute URL of this asset
     * is in base URL
     * @return bool|null
     */
    public function isInBaseUrl()
    {
        $absoluteUrl = $this->getAbsoluteUrl();

        if (is_null($absoluteUrl)) {
            return null;
        }

        if (strpos($absoluteUrl, $this->baseUrl) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Debug
     * @param bool $simple
     * @throws \Exception
     */
    public function printTree($simple = false)
    {

        if (!$simple) {
            $domElementNodeName = '';
            $domElementContent = '';
            if ($this->fromDomElement) {
                $domElementNodeName = substr($this->fromDomElement->nodeName, 0, 200);
                $domElementContent = substr($this->fromDomElement->nodeValue, 0, 200);
            }
            echo '<fieldset style="font-family: monospace; margin: 15px 0;">';
            echo '<legend style="font-weight: bold;">' . $this->getAbsoluteUrl() . '</legend>';
            echo '<div><b>Type:</b> ' . get_class($this) . '</div>';

            echo '<div><b>Extract filter:</b> ' . $this->extractFilter . '</div>';
            echo '<div><b>From string:</b> ' . substr($this->fromString, 0, 200) . '</div>';
            echo '<div><b>From DomElement:</b> ' . $domElementNodeName . ' - ' . $domElementContent . '</div>';
            echo '<div><b>Raw URL:</b> ' . $this->url . '</div>';
            echo '<div><b>Absolute URL:</b> ' . $this->getAbsoluteUrl() . '</div>';
            echo '<div><b>Relative URL:</b> ' . $this->getExportUrl() . '</div>';
            echo '<div><b>Relative path:</b> ' . $this->getRelativePath() . '</div>';
            echo '<div><b>File get contents Path:</b> ' . $this->getSourcePath() . '</div>';
            echo '<div><b>Base URL:</b> ' . $this->baseUrl . '</div>';
            echo '<div><b>Base Path:</b> ' . $this->basePath . '</div>';
            echo '<div><b>In base URL:</b> ' . $this->isInBaseUrl() . '</div>';
            echo '<div><b>Export path:</b> ' . $this->getExportPath() . '</div>';
            echo '<div><b>Export URL:</b> ' . $this->getExportUrl() . '</div>';
            //echo '<div><b>Content:</b> ' . substr($this->content, 0, 200) . '</div>';
            echo '<div><b>Content:</b> ' . htmlentities(substr($this->getContent(), 0, 200)) . '</div>';
            foreach ($this->children as $child) {
                $child->printTree($simple);
            }

            echo '</fieldset>';
        } else {
            echo '<div style="font-family: monospace">';
            $function = new \ReflectionClass($this);
            echo '<b>' . $function->getShortName() . '</b> -- ' . $this->url . ' --> ' . $this->getExportUrl();
            echo '<ul>';
            foreach ($this->children as $child) {
                echo '<li>';
                $child->printTree($simple);
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }

    /**
     * Return content of this file/asset (file_get_contents)
     * only if it's in the base URL
     * @return bool|string
     * @throws \Exception
     */
    public function retrieveContent()
    {
        if (!$this->isInBaseUrl()) {
            return null;
        }
        $path = $this->getSourcePath();

        // @TODO: How to deal with unreachable assets ?
        return @file_get_contents($path);
    }

    /**
     * Replace URL of this asset in the content this asset come from
     * And call the same method on its children
     */
    public function updateInitiatorContent()
    {
        foreach ($this->children as $child) {
            $child->updateInitiatorContent();
        }
    }

    /**
     * Replace DomElement with $replace
     * Used only in HtmlAsset
     * @param DOMElement $domElement
     * @param string     $replace
     * @return null
     */
    public function replaceDomElement($domElement, $replace)
    {
        return null;
    }

    /**
     * Replace $search by $replace in the content of this asset
     * @param string     $search
     * @param string     $replace
     * @param Asset|null $asset
     */
    public function replaceInContent($search, $replace, $asset = null)
    {
        $this->_content = str_replace($search, $replace, $this->_content);
        if ($this->initiator) {
            $this->initiator->replaceInContent($search, $replace, $asset);
        }
    }

    /**
     * Return true if this asset has asset child
     */
    public function hasChild()
    {
        return count($this->children) > 0;
    }

    /**
     * Set content of this asset
     * @param string $content
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * Return content of this asset
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Retrieve and update content
     * @throws \Exception
     */
    protected function retrieveAndUpdateContent()
    {
        $this->_content = $this->retrieveContent();
    }

    /**
     * Set fromDomElement recursively
     * @param $domElement
     */
    protected function setRecursiveFromDomElement($domElement)
    {
        $this->fromDomElement = $domElement;
        foreach ($this->children as $child) {
            $child->setRecursiveFromDomElement($domElement);
        }
    }
}