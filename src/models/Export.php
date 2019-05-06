<?php

namespace lhs\craftpageexporter\models;

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

    /** @var Asset[] */
    public $rootAssets = [];

    /** @var string Base URL: only assets under this path will be exported */
    public $baseUrl = null;

    /** @var \Closure|null Function to transform absolute URLs into paths/URLs used to retrieve their content */
    public $sourcePathTransformer = null;

    /** @var bool Inline styles in HTML */
    public $inlineStyles = false;

    /** @var bool Inline scripts in HTML */
    public $inlineScripts = false;

    /** @var BaseTransformer[] Transformation to apply to assets */
    public $transformers = [];

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
     * @param $pageName
     * @param $pageUrl
     * @param $content
     * @return $this
     * @throws \Exception
     */
    public function addPage($pageName, $pageUrl, $content)
    {
        $currentPageAsset = new HtmlAsset([
            'name'       => $pageName . '.html',
            'url'        => $pageUrl,
            'fromString' => $content,
            'export'     => $this,
            'baseUrl'    => $this->baseUrl,
            'basePath'   => $this->baseUrl,
        ]);

        $currentPageAsset->populateChildren();

        // Add current page to asset collection
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
     * Transform asset tree
     */
    public function transform()
    {
        foreach ($this->transformers as $transformer) {
            foreach ($this->rootAssets as $rootAsset) {
                $this->applyTransformer($rootAsset, $transformer);
            }
        }
        foreach ($this->rootAssets as $rootAsset) {
            $rootAsset->updateInitiatorContent();
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
     * Default function used to transform the url of ``$âsset`` to path used for getting the content of $asset
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
            $asset->getAbsoluteUrl()
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

