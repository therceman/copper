<?php

use Copper\Component\Error\ErrorConfigurator;
use Copper\Kernel;

/**
 * @param ErrorConfigurator $errorConfig
 */
return function (ErrorConfigurator $errorConfig) {

    $errorConfig->view = true;
    $errorConfig->view_route_redirect = true;
    $errorConfig->view_route_name = 'error_handler_route';
    $errorConfig->view_route_path = '/app/error';
    $errorConfig->view_default_template = 'error';
    $errorConfig->log = true;
    $errorConfig->e_log_format = '[%1$s] - "%2$s %3$s %4$s" %5$s - [%6$s] %7$s (%8$s @ %9$s) | %10$s | %11$s | %12$s';
    $errorConfig->app_log_format = '[%1$s] - "%2$s %3$s %4$s" %5$s - [%6$s] %7$s | %10$s | %11$s | %12$s';
    $errorConfig->app_error_type = 'ApplicationError';
    $errorConfig->log_filepath = Kernel::getAppLogPath('error.log');

};