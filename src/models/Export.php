<?php

namespace lhs\craftpageexporter\models;

use Craft;
use craft\helpers\UrlHelper;
use Exception;
use lhs\craftpageexporter\models\transformers\BaseTransformer;
use yii\base\Component;


/**
 * Class Export
 * One export can contains one or multiple HTML pages
 * @package lhs\craftpageexporter\models
 */
class Export extends Component
{
    /**
     * @see Settings::$baseUrl
     * @var ?string
     */
    public ?string $baseUrl = null;

    /**
     * @see Settings::$sourcePathTransformer
     * @var ?callable
     */
    public $sourcePathTransformer;

    /**
     * @see Settings::$inlineStyles
     * @var bool
     */
    public bool $inlineStyles = false;

    /**
     * @see Settings::$inlineScripts
     * @var bool
     */
    public bool $inlineScripts = false;

    /**
     * @see Settings::$customSelectors
     * @var array
     */
    public array $customSelectors = [];

    /**
     * Transformations to apply to assets
     * @var BaseTransformer[]
     */
    public array $transformers = [];

    /**
     * Whether throw exception if an asset file is not found
     * @var bool
     */
    public bool $failOnFileNotFound = false;

    /**
     * Collection of root assets composing this export
     * @var Asset[]
     */
    protected array $rootAssets = [];


    /**
     * Init
     */
    public function init(): void
    {
        if (!$this->sourcePathTransformer) {
            $export = $this;
            $this->sourcePathTransformer = static function ($asset) use ($export) {
                return $export->defaultSourcePathTransformer($asset);
            };
        }
    }

    /**
     * @param string $pageName
     * @param string $pageUrl
     * @param string $content
     * @return $this
     * @throws Exception
     */
    public function addPage(string $pageName, string $pageUrl, string $content): static
    {
        $currentPageAsset = new HtmlAsset([
            'name'            => $pageName . '.html',
            'url'             => $pageUrl,
            'fromString'      => $content,
            'export'          => $this,
            'baseUrl'         => $this->baseUrl,
            'basePath'        => $this->baseUrl,
            'customSelectors' => $this->customSelectors,
        ]);

        $currentPageAsset->populateChildren();
        $this->addRootAsset($currentPageAsset);

        return $this;
    }

    /**
     * @param Asset $rootAsset
     */
    public function addRootAsset(Asset $rootAsset): void
    {
        $this->rootAssets[] = $rootAsset;
    }

    /**
     * @return Asset[]
     */
    public function getRootAssets(): array
    {
        return $this->rootAssets;
    }

    /**
     * Transform asset tree
     */
    public function transform(): void
    {
        foreach ($this->transformers as $transformer) {
            foreach ($this->rootAssets as $rootAsset) {
                $this->applyTransformer($rootAsset, $transformer);
            }
        }
    }

    /**
     * Debug function
     *
     * @param bool $simple
     * @throws Exception
     */
    public function printTree(bool $simple = false): void
    {
        foreach ($this->rootAssets as $rootAsset) {
            $rootAsset->printTree($simple);
        }
    }

    /**
     * Default function used to transform the url of ``$asset`` to path used for getting the content of $asset
     * This function transform URL to path relative to @``webroot``
     * Can be overriden by ``sourcePathTransformer`` attribute
     *
     * @param Asset $asset
     * @return string
     */
    protected function defaultSourcePathTransformer(Asset $asset): string
    {
        return str_replace(
            Craft::getAlias('@web'),
            Craft::getAlias('@webroot/'),
            strtok($asset->initialAbsoluteUrl, '?') // Discard the query string
        );
    }

    /**
     * Apply transformer recursively
     *
     * @param Asset           $asset
     * @param BaseTransformer $transformer
     */
    protected function applyTransformer(Asset $asset, BaseTransformer $transformer): void
    {
        $transformer->transform($asset);
        foreach ($asset->children as $child) {
            $this->applyTransformer($child, $transformer);
        }
    }
}

