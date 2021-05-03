<?php


namespace Copper\Component\Error;

/**
 * Class ErrorConfigurator
 * @package Copper\Component\Error
 */
class ErrorConfigurator
{
    /**
     * @var bool Enable error view template with detailed description
     * Default: true
     */
    public $view;
    /**
     * @var bool If view is enabled - force redirect to error route to display view (instead of direct output)
     * Default: true
     */
    public $view_route_redirect;
    /**
     * @var string Error view route name
     * Default: 'error_handler_route'
     */
    public $view_route_name;
    /**
     * @var string Error view route path
     * Default: '/app/error'
     */
    public $view_route_path;
    /**
     * @var string Error view template
     * Default: 'error'
     */
    public $view_default_template;

    /**
     * @var bool Enable error logging to file
     * Default: true
     */
    public $log;
    /**
     * @var string Exception error log format
     * <hr>
     * %1$s - Current Date & Time. e.g. 2021-05-03 14:31:17 <br>
     * %2$s - Request Method. e.g POST <br>
     * %3$s - Request URL. e.g. /example.com/demo?a=123 <br>
     * %4$s - Request Protocol Version. e.g HTTP/1.1 <br>
     * %5$s - Response Status. e.g. 404 <br>
     * %6$s - Error Type. e.g. ApplicationError <br>
     * %7$s - Error Message. e.g. Error! <br>
     * %8$s - File where exception occurred. e.g. {PROJECT}/public/index.php <br>
     * %9$s - File line where exception occurred. e.g. 40 <br>
     * %10$s - User IP addresses. e.g. 192.168.0.1 <br>
     * %11$s - Logged-In User ID. e.g. 1 <br>
     * %12$s - Request Referer (from where user came from). e.g. https://google.com <br>
     * <br>
     * Default: '[%1$s] - "%2$s %3$s %4$s" %5$s - [%6$s] %7$s (%8$s @ %9$s) | %10$s | %11$s | %12$s'
     */
    public $e_log_format;
    /**
     * @var string Application error log format
     * Default: '[%1$s] - "%2$s %3$s %4$s" %5$s - [%6$s] %7$s | %10$s | %11$s | %12$s'
     */
    public $app_log_format;
    /**
     * @var string Application error type
     * Default: 'ApplicationError'
     */
    public $app_error_type;
    /**
     * @var string Error logging file path
     * Default: '{PROJECT}/log/error.log'
     */
    public $log_filepath;

    /**
     * @var bool Hide project path from file path where exception occurred. <br>
     * E.g. /opt/lampp/htdocs/project/public/index.php -> /public/index.php
     * Default: true
     */
    public $e_hide_project_path = true;
}