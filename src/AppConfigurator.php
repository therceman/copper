<?php


namespace Copper;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class AppConfigurator
 * @package Copper
 */
class AppConfigurator
{
    /**
     * Enable Development Mode.
     * <hr>
     * On Application Start:
     * <br>
     * <code>
     * - generate .phpstorm.meta.php (template name/assets/config->bag autocomplete)
     * </code>
     * @var bool
     */
    public $dev_mode;

    /**
     * @var string
     */
    public $version;

    /**
     * Application title
     * @var string|array
     */
    public $title;

    /**
     * Application description
     * @var string|array
     */
    public $description;

    /**
     * Application author
     * @var string
     */
    public $author;

    /**
     * Bag for custom config keys
     * <hr>
     * <code>
     * - bag->set('tags', ['Blog', 'Tech', 'Web Development'])
     * - bag->get('tags')
     * </code>
     * @var ParameterBag
     */
    public $bag;

    /**
     * Default Date & Time Timezone (DateHandler)
     * <hr>
     * Example: 'Europe/Riga'
     * <br>
     * Default: false
     *
     * @var string|false
     */
    public $timezone;

    /**
     * Default Time format (for DateHandler)
     * <hr>
     * Example: 'm-d-Y'
     * <br>
     * Default: 'Y-m-d'
     * @var string
     */
    public $dateFormat;

    /**
     * Default Time format (for DateHandler)
     * <hr>
     * Default: 'H:i:s'
     * @var string
     */
    public $timeFormat;

    /**
     * Default Time format (for DateHandler)
     * <hr>
     * Example: 'm-d-Y H:i:s'
     * <br>
     * Default: 'Y-m-d H:i:s'
     * @var string
     */
    public $dateTimeFormat;

    /**
     * @var int [default] = -1
     * <p>Default serialize precision</p>
     * <p>This is primary intended to fix json_encode bug when encoding float/decimal numbers</p>
     * <p>Setting this to (-1) - floats will be encoded as expected, without huge amount of decimals</p>
     * <p>Note: PHP default value is 100</p>
     */
    public $serialize_precision;

    /**
     * Trims all input to controller from request query, body, json
     * <hr>
     * All whitespaces (+unicode whitespaces)
     * and tabs are removed
     * <hr>
     * Default: true
     *
     * @var bool
     */
    public $trim_input;

    /**
     * Relative path to public folder
     *
     * <hr>
     * Default: public
     *
     * @var string
     */
    public $public_rel_path;

    /**
     * Relative path to data folder
     *
     * <hr>
     * Default: data
     *
     * @var string
     */
    public $data_rel_path;

    public function __construct()
    {
        $this->bag = new ParameterBag();
    }
}