<?php


use Copper\AppConfigurator;

return function (AppConfigurator $appConfig) {
    $appConfig->dev_mode = false; // TODO
    $appConfig->version = '1.0.0';

    $appConfig->title = 'Copper PHP Framework';
    $appConfig->description = 'Copper is a PHP Framework that is mainly focused on simplicity and development speed';
    $appConfig->author = 'Anton (therceman)';
    $appConfig->bag->set('keywords', ['PHP', 'Framework', 'CopperPHP', 'Web Development']);

    $appConfig->timezone = false;

    $appConfig->dateFormat = 'Y-m-d';
    $appConfig->timeFormat = 'H:i:s';
    $appConfig->dateTimeFormat = 'Y-m-d H:i:s';

    $appConfig->serialize_precision = -1;
    $appConfig->trim_input = true;

    $appConfig->public_rel_path = 'public';
};