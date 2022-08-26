<?php

namespace lhs\craftpageexporter\models;

use craft\base\Component;
use craft\helpers\UrlHelper;
use DOMElement;
use DOMNode;
use Exception;
use lhs\craftpageexporter\helpers\PhpUri;
use ReflectionClass;

/**
 * Class Asset
 * @package lhs\craftpageexporter\models
 */
abstract class Asset extends Component
{
    /**
     * BaseURL and BasePath are differents in StyleAsset
     * For images, path is relative to basePath and url is relative to baseURL (url of the stylesheet file)
     */
    /** @var ?string Base URL: only assets under this path urll be exported, used to calculate relative url */
    public ?string $baseUrl = null;

    /** @var ?string Path this asset is relative to, used to calculate relative path */
    public ?string $basePath = null;

    /** @var ?string From string as found in the sources */
    public ?string $fromString = null;

    /** @var ?DOMNode If the initiator is HtmlAsset, this is the DomNode referencing this asset */
    public ?DOMNode $fromDomElement = null;

    /** @var Asset[] Extracted children of this asset */
    public array $children = [];

    /** @var ?Asset Who initiate this asset */
    public ?Asset $initiator = null;

    /** @var ?string How this asset was extracted */
    public ?string $extractFilter = null;

    /** @var ?string $url URL of this asset (can be transformed) */
    public ?string $url = null;

    /** @var string|null $url URL as found in sources (if this asset has URL) */
    public ?string $initialUrl = null;

    /** @var string|null $url URL as found in sources (if this asset has URL) */
    public ?string $initialAbsoluteUrl = null;

    /** @var ?Export The export object to which this asset belongs */
    public ?Export $export = null;

    /** @var ?string Export path */
    public ?string $exportPath;

    /** @var string Export UrL */
    public string $exportUrl;

    /** @var ?HtmlAsset */
    protected ?HtmlAsset $rootAsset = null;

    /** @var string|null Content of this asset */
    protected ?string $_content = null;

    /** @var bool Whether this asset will be present in the resulting archive */
    public bool $willBeInArchive = false;


    /**
     * Asset init
     * Define default configuration and retrieve the content of this asset
     * @inheritdoc
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        if (!$this->export) {
            if (!$this->initiator) {
                throw new Exception('The export attribute must be defined for the root asset.');
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

        // Default export path
        $this->exportPath = $this->getRelativePath();

        // Keep the initial URL
        $this->initialUrl = $this->url;
        $this->initialAbsoluteUrl = $this->getAbsoluteUrl();

        $this->retrieveAndUpdateContent();

        // If this asset is in base url, it should be in the archive
        $this->willBeInArchive = $this->isInBaseUrl();

        // Replace relative URL with absolute URL in content
        if ($this->getAbsoluteUrl()) {
            $this->setExportUrl($this->getAbsoluteUrl());
        }
    }

    /**
     * Add a child on this asset
     * @param Asset $child
     */
    public function addChild(Asset $child): void
    {
        $this->children[] = $child;
    }

    /**
     * Remove child
     * @param Asset $asset
     */
    public function removeChild(Asset $asset): void
    {
        foreach ($this->children as $key => $child) {
            if ($child === $asset) {
                unset($this->children[$key]);
            }
        }
    }

    /**
     * Parse this asset content and create children assets
     * @return void
     */
    public function populateChildren(): void
    {
    }

    /**
     * Return path of this asset in the export
     * @return ?string
     */
    public function getExportPath(): ?string
    {
        return $this->exportPath;
    }

    /**
     * @param string $exportPath
     */
    public function setExportPath(string $exportPath): void
    {
        $this->exportPath = $exportPath;
    }

    /**
     * Return URL of this asset in the export
     * @return ?string
     */
    public function getExportUrl(): ?string
    {
        return $this->exportUrl;
    }

    /**
     * @param string $exportUrl
     */
    public function setExportUrl(string $exportUrl): void
    {
        $this->exportUrl = $exportUrl;
        $this->updateInitiatorContent();
        $this->url = $this->exportUrl;
    }

    /**
     * Return the path (file or URL) used for getting the content of this asset
     * @return ?string
     * @throws Exception
     */
    public function getSourcePath(): ?string
    {
        // Use overridden transform method
        if (!is_callable($this->export->sourcePathTransformer)) {
            throw new Exception('sourcePathTransformer function of the export is not callable.');
        }

        return ($this->export->sourcePathTransformer)($this);
    }

    /**
     * Return the absolute URL of this asset
     * Return null if this asset hasn't URL
     * @return ?string
     */
    public function getAbsoluteUrl(): ?string
    {
        if (empty($this->url)) {
            return null;
        }

        if (UrlHelper::isAbsoluteUrl($this->url)) {
            return $this->url;
        }

        $parentUrl = $this->getBaseUrl();
        return PhpUri::parse($parentUrl)->join($this->url);
    }

    /**
     * Return path of this asset relative to basePath (the root of this export)
     * Return null if this asset hasn't URL
     * @return ?string
     */
    public function getRelativePath(): ?string
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
     * @return ?string
     */
    public function getRelativeUrl(): ?string
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
     * @param $base
     * @param $absoluteUrl
     * @return string
     */
    public function calculateRelativePath($base, $absoluteUrl): string
    {
        // Remove empty items
        $splitBaseUrl = array_filter(explode('/', $base));
        $splitAbsoluteUrl = array_filter(explode('/', $absoluteUrl));

        // Reorder keys
        $splitBaseUrl = array_values($splitBaseUrl);
        $splitAbsoluteUrl = array_values($splitAbsoluteUrl);

        // Not in the same domain return the original absolute url
        if ($splitBaseUrl[0] !== $splitAbsoluteUrl[0] || $splitBaseUrl[1] !== $splitAbsoluteUrl[1]) {
            return $absoluteUrl;
        }

        foreach ($splitBaseUrl as $key => $item) {
            if ($item === $splitAbsoluteUrl[$key]) {
                unset($splitBaseUrl[$key], $splitAbsoluteUrl[$key]);
            } else {
                break;
            }
        }

        $splitBackwards = array_fill(0, count($splitBaseUrl), '..');
        $splitFull = $splitBackwards + $splitAbsoluteUrl;

        return implode('/', $splitFull);
    }

    /**
     * @param $parentUrl
     * @param $url
     * @return string
     */
    public function calculateAbsoluteUrl($parentUrl, $url): string
    {
        return PhpUri::parse($parentUrl)->join($url);
    }

    /**
     * Return true if the absolute URL of this asset
     * is in root asset base URL
     * @return ?bool
     */
    public function isInBaseUrl(): ?bool
    {
        $absoluteUrl = $this->getAbsoluteUrl();

        if (is_null($absoluteUrl)) {
            return null;
        }

        // If no root asset, we're the root asset, so we're in the base URL
        if (!$this->getRootAsset()) {
            return true;
        }

        return str_starts_with($absoluteUrl, $this->getRootAsset()->getBaseUrl());
    }

    /**
     * Return content of this file/asset (file_get_contents only if it's in the
     * base URL
     * @return bool|string|null
     * @throws Exception
     */
    public function retrieveContent(): bool|string|null
    {
        if (!$this->isInBaseUrl()) {
            return null;
        }
        $path = $this->getSourcePath();


        if ($this->export->failOnFileNotFound && !file_exists($path)) {
            throw new Exception('Asset file not found: "' . $path . '"');
        }

        return @file_get_contents($path);
    }

    /**
     * Replace URL of this asset in the content this asset come from
     * And call the same method on its children
     */
    public function updateInitiatorContent(): void
    {
        foreach ($this->children as $child) {
            $child->updateInitiatorContent();
        }
    }

    /**
     * Replace current asset URL with the new export URL in the initiator content
     */
    public function replaceUrlWithExportUrlInInitiator(): void
    {
        $exportUrl = $this->getExportUrl();

        // No replacement needed
        if (!$this->willBeInArchive || !$this->url || !$exportUrl || !$this->initiator) {
            return;
        }

        $this->initiator->replaceInContent($this->url, $exportUrl, $this);
    }

    /**
     * Replace DomElement with $replace
     * Used only in HtmlAsset
     *
     * @param DOMNode $domElement
     * @param DOMNode $replace
     * @return void
     */
    public function replaceDomElement(DOMNode $domElement, DOMNode $replace): void
    {
    }

    /**
     * Replace $search by $replace in the content of this asset
     *
     * @param string     $search
     * @param string $replace
     * @param ?Asset $asset
     */
    public function replaceInContent(string $search, string $replace, ?Asset $asset = null): void
    {
        $this->_content = str_replace($search, $replace, $this->_content);
        $this->initiator?->replaceInContent($search, $replace, $asset);
    }

    /**
     * Return true if this asset has asset child
     * @return bool
     */
    public function hasChild(): bool
    {
        return count($this->children) > 0;
    }

    /**
     * Set content of this asset
     *
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->_content = $content;
    }

    /**
     * Return content of this asset
     *
     * @return ?string
     */
    public function getContent(): ?string
    {
        return $this->_content;
    }

    /**
     * Retrieve and update content
     * @throws Exception
     */
    protected function retrieveAndUpdateContent(): void
    {
        $this->_content = $this->retrieveContent();
    }

    /**
     * @return ?HtmlAsset
     */
    public function getRootAsset(): ?HtmlAsset
    {
        return $this->rootAsset;
    }

    /**
     * @param ?HtmlAsset $rootAsset
     */
    public function setRootAsset(?HtmlAsset $rootAsset): void
    {
        $this->rootAsset = $rootAsset;
    }

    /**
     * @return ?string
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * Update base URL on current asset and its children
     *
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
        foreach ($this->children as $child) {
            $child->setBaseUrl($baseUrl);
        }
    }

    /**
     * Set fromDomElement recursively
     * @param ?DOMElement $domElement
     */
    protected function setRecursiveFromDomElement(?DOMElement $domElement): void
    {
        $this->fromDomElement = $domElement;
        foreach ($this->children as $child) {
            $child->setRecursiveFromDomElement($domElement);
        }
    }

    /**
     * Debug
     * @param bool $simple
     * @throws Exception
     */
    public function printTree(bool $simple = false): void
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
            echo '<div><b>From string:</b> ' . substr(htmlentities($this->fromString), 0, 200) . '</div>';
            echo '<div><b>From DomElement:</b> ' . $domElementNodeName . ' - ' . $domElementContent . '</div>';
            echo '<div><b>Raw URL:</b> ' . $this->url . '</div>';
            echo '<div><b>Absolute URL:</b> ' . $this->getAbsoluteUrl() . '</div>';
            echo '<div><b>Relative URL:</b> ' . $this->getExportUrl() . '</div>';
            echo '<div><b>Relative path:</b> ' . $this->getRelativePath() . '</div>';
            echo '<div><b>File get contents Path:</b> ' . $this->getSourcePath() . '</div>';
            echo '<div><b>Base URL:</b> ' . $this->baseUrl . '</div>';
            echo '<div><b>Base Path:</b> ' . $this->basePath . '</div>';
            echo '<div><b>In base URL:</b> ' . $this->isInBaseUrl() . '</div>';
            echo '<div><b>willBeInArchive:</b> ' . $this->willBeInArchive . '</div>';
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
            $function = new ReflectionClass($this);
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
}
