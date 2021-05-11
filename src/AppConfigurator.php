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
     * Application title
     * @var string
     */
    public $title;

    /**
     * Application description
     * @var string
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
     * @var string|false
     */
    public $timezone;

    /**
     * @var int [default] = -1
     * <p>Default serialize precision</p>
     * <p>This is primary intended to fix json_encode bug when encoding float/decimal numbers</p>
     * <p>Setting this to (-1) - floats will be encoded as expected, without huge amount of decimals</p>
     * <p>Note: PHP default value is 100</p>
     */
    public $serialize_precision;

    public function __construct()
    {
        $this->bag = new ParameterBag();
    }
}