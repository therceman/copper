<?php


use Copper\AppConfigurator;
use Copper\Kernel;

return function (AppConfigurator $app) {

    $app->error_view = true;
    $app->error_view_route = 'error';
    $app->error_view_route_redirect = true;
    $app->error_view_default_template = 'error';
    $app->error_log = true;
    $app->error_log_format = '[%1$s] - %2$s %3$s - [%4$s] %5$s (%6$s @ %7$s - %8$s => %9$s) | %10$s | %11$s | %12$s';
    $app->error_log_filepath = Kernel::getProjectLogPath('error.log');

};