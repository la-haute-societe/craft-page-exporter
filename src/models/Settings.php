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

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 * @since     1.0.0
 */
class Settings extends Model
{
    /**
     * Inline styles inside HTML.
     */
    public bool $inlineStyles = true;

    /**
     * Inline scripts inside HTML.
     */
    public bool $inlineScripts = true;

    /**
     * Format of the asset URL in export.
     * Twig variables available :
     */
    public ?string $exportUrlFormat = '{dirname}/{basename}';

    /**
     * Format of the asset path in export.
     * Twig variables available :
     */
    public ?string $exportPathFormat = '{dirname}/{basename}';

    /**
     * Callable function which should return the HTML content of one entry.
     * If not defined, HTML content will be extracted using the Craft CMS
     * rendering method.
     * @var null|callable
     */
    public $entryContentExtractor;

    /**
     * Collection of callable functions
     * @var null|callable[]
     */
    public ?array $assetTransformers = null;

    /**
     * Whether throw exception if an asset file is not found.
     * In the case a file is not found and this param is false, the file will be
     * empty.
     */
    public bool $failOnFileNotFound = false;

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
     */
    public array $customSelectors = [];

    /**
     * Function used to transform absolute URLs into paths/URLs in order to
     * retrieve their content.
     * @var null|callable
     */
    public $sourcePathTransformer;

    /**
     * Exported archive filename
     * @var string
     */
    public string $archiveName = 'export';
}
