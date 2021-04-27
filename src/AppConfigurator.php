<?php


namespace Copper;

class AppConfigurator
{
    /** @var bool Enable error view template with detailed description */
    public $error_view;
    /** @var bool Error view route */
    public $error_view_route;
    /** @var bool Error view template */
    public $error_view_default_template;
    /** @var bool Enable error logging to file */
    public $error_log;
    /** @var string */
    public $error_log_format;
    /** @var string Error logging file path */
    public $error_log_filepath;
}