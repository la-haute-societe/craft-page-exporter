<?php
/**
 * craft-page-exporter plugin for Craft CMS 3.x
 *
 * Craft page exporter
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2019 La Haute Société
 */

namespace lhs\craftpageexporter\models;

use craft\base\Model;
use craft\helpers\UrlHelper;

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 * @since     1.0.0
 */
class Settings extends Model
{
    /**
     * Base URL
     * Default: baseRequestUrl()
     * @var null
     */
    public $baseUrl = null;

    /**
     * Inline styles inside HTML.
     * Default: true
     * @var bool
     */
    public $inlineStyles = true;

    /**
     * Inline scripts inside HTML.
     * Default: true
     * @var bool
     */
    public $inlineScripts = true;

    /**
     * Flatten all assets file path,
     * all assets will be export to root folder.
     * Default: true
     * @var bool
     */
    public $flatten = true;

    /**
     * String used to prefix the exported URLs.
     * If not defined, no prefix will be used.
     * You can use twig templating in this string.
     * Default: null
     * @var null|string
     */
    public $prefixExportUrl = null;

    /**
     * Callable function which should return the
     * HTML content of one entry.
     * If not defined, HTML content will be extracted
     * using the CraftCMS rendering method.
     * Default: null
     * @var null|callable
     */
    public $entryContentExtractor = null;

    /**
     * Collection of callable functions
     * Default: null
     * @var null|callable[]
     */
    public $assetTransformers = null;


    /**
     * Init
     */
    public function init()
    {
        parent::init();

        // Default value for base URL
        if (is_null($this->baseUrl)) {
            $this->baseUrl = UrlHelper::baseRequestUrl();
        }
    }
}
