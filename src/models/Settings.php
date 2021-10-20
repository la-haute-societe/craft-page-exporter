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
     * Format of the asset URL in export.
     * Twig variables available :
     * Default: null
     * @var null|string
     */
    public $exportUrlFormat = '{dirname}/{basename}';

    /**
     * Format of the asset path in export.
     * Twig variables available :
     * Default: null
     * @var null|string
     */
    public $exportPathFormat = '{dirname}/{basename}';

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
     * Whether throw exception if an asset file is not found
     * In the case a file is not found and this param is false, the file will be empty.
     * Default: false
     * @var bool
     */
    public $failOnFileNotFound = false;

    /**
     * List of custom selectors in the following format.
     * - ``selectors`` are XPath expressions (http://xmlfr.org/w3c/TR/xpath/).
     * - ``assetClass`` is the class which will manage the content found by the XPath selectors.
     *
     * Example:
     * ```php
     * [
     *    [
     *      'selectors'  => [
     *          '//video/@poster',
     *          '//tag/@whatever',
     *       ],
     *       'assetClass' => MiscAsset::class,
     *    ]
     * ],
     * ```
     *
     * @var array
     */
    public $customSelectors = [];

    /**
     * Function used to to transform absolute URLs into paths/URLs
     * in order to retrieve their content
     * @var null|callable
     */
    public $sourcePathTransformer = null;

    /**
     * Exported archive filename
     * Default: 'export'
     * @var string
     */
    public $archiveName = 'export';
}
