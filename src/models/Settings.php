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
use lhs\craftpageexporter\models\transformers\BaseTransformer;

/**
 * @author    La Haute Société
 * @package   Craftpageexporter
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /** @var string */
    public $baseUrl;

    /** @var bool */
    public $inlineStyles;

    /** @var bool */
    public $inlineScripts;

    /** @var BaseTransformer */
    public $transformers;
}
