<?php

namespace lhs\craftpageexporter\models;

use craft\elements\Entry;
use craft\helpers\UrlHelper;
use lhs\craftpageexporter\models\transformers\BaseTransformer;
use Yii;
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
     * @var string
     */
    public $baseUrl = null;

    /**
     * @see Settings::$sourcePathTransformer
     * @var null|callable
     */
    public $sourcePathTransformer = null;

    /**
     * @see Settings::$inlineStyles
     * @var bool
     */
    public $inlineStyles = false;

    /**
     * @see Settings::$inlineScripts
     * @var bool
     */
    public $inlineScripts = false;

    /**
     * @see Settings::$customSelectors
     * @var array
     */
    public $customSelectors = [];

    /**
     * Transformations to apply to assets
     * @var BaseTransformer[]
     */
    public $transformers = [];

    /**
     * Whether throw exception if an asset file is not found
     * @var bool
     */
    public $failOnFileNotFound = false;

    /**
     * Collection of root assets composing this export
     * @var Asset[]
     */
    protected $rootAssets = [];


    /**
     * Init
     */
    public function init()
    {
        if (!$this->sourcePathTransformer) {
            /** @var Export $export */
            $export = $this;
            $this->sourcePathTransformer = function ($asset) use ($export) {
                return $export->defaultSourcePathTransformer($asset);
            };
        }
    }

    /**
     * @param string $pageName
     * @param string $pageUrl
     * @param string $content
     * @param Entry  $entry
     * @return $this
     * @throws \Exception
     */
    public function addPage($pageName, $pageUrl, $content, $entry)
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
    public function addRootAsset($rootAsset)
    {
        $this->rootAssets[] = $rootAsset;
    }

    /**
     * @return Asset[]
     */
    public function getRootAssets()
    {
        return $this->rootAssets;
    }

    /**
     * Transform asset tree
     */
    public function transform()
    {
        foreach ($this->transformers as $transformer) {
            foreach ($this->rootAssets as $rootAsset) {
                $this->applyTransformer($rootAsset, $transformer);
            }
        }
    }

    /**
     * Debug function
     * @param bool $simple
     * @throws \Exception
     */
    public function printTree($simple = false)
    {
        foreach ($this->rootAssets as $rootAsset) {
            $rootAsset->printTree($simple);
        }
    }

    /**
     * Default function used to transform the url of ``$asset`` to path used for getting the content of $asset
     * This function transform URL to path relative to @``webroot``
     * Can be overriden by ``sourcePathTransformer`` attribute
     * @param Asset $asset
     * @return string
     */
    protected function defaultSourcePathTransformer($asset)
    {
        return str_replace(
            UrlHelper::baseRequestUrl(),
            Yii::getAlias('@webroot/'),
            $asset->initialAbsoluteUrl
        );
    }

    /**
     * Apply transformer recursively
     * @param Asset           $asset
     * @param BaseTransformer $transformer
     */
    protected function applyTransformer(Asset $asset, $transformer)
    {
        $transformer->transform($asset);
        foreach ($asset->children as $child) {
            $this->applyTransformer($child, $transformer);
        }
    }

}

