<?php


use Copper\AppConfigurator;
use Copper\Kernel;

return function (AppConfigurator $appConfig) {

    $appConfig->dev_mode = false; // TODO

    $appConfig->title = 'Copper PHP Framework';
    $appConfig->description = 'Copper is a PHP Framework that is mainly focused on simplicity and development speed';
    $appConfig->author = '@therceman';
    $appConfig->bag->set('keywords', ['PHP', 'Framework', 'CopperPHP', 'Web Development']);

    $appConfig->error_view = true;
    $appConfig->error_view_route = 'error';
    $appConfig->error_view_route_redirect = true;
    $appConfig->error_view_default_template = 'error';
    $appConfig->error_log = true;
    $appConfig->error_log_format = '[%1$s] - %2$s %3$s - [%4$s] %5$s (%6$s @ %7$s - %8$s => %9$s) | %10$s | %11$s | %12$s';
    $appConfig->error_log_filepath = Kernel::getProjectLogPath('error.log');

};