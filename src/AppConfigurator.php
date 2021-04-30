<?php


namespace Copper;

use Symfony\Component\HttpFoundation\ParameterBag;

class AppConfigurator
{
    /** @var bool Enable Development Mode.
     * <hr>
     * On Application Start:
     * <br>
     * <code>
     * - generate .phpstorm.meta.php (template name/assets/config->bag autocomplete)
     * </code>
     */
    public $dev_mode;

    /** @var string */
    public $title;
    /** @var string */
    public $description;
    /** @var string */
    public $author;
    /** Bag for custom config keys
     * <hr>
     * <code>
     * - bag->set('tags', ['Blog', 'Tech', 'Web Development'])
     * - bag->get('tags')
     * </code>
     * @var ParameterBag */
    public $bag;

    /** @var bool Enable error view template with detailed description */
    public $error_view;
    /** @var bool Error view route */
    public $error_view_route;
    /** @var bool Redirect to error route */
    public $error_view_route_redirect;
    /** @var bool Error view template */
    public $error_view_default_template;
    /** @var bool Enable error logging to file */
    public $error_log;
    /** @var string */
    public $error_log_format;
    /** @var string Error logging file path */
    public $error_log_filepath;
}