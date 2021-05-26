<?php


use Copper\AppConfigurator;
use Copper\Kernel;

return function (AppConfigurator $appConfig) {

    $appConfig->dev_mode = false; // TODO

    $appConfig->title = 'Copper PHP Framework';
    $appConfig->description = 'Copper is a PHP Framework that is mainly focused on simplicity and development speed';
    $appConfig->author = 'Anton (therceman)';
    $appConfig->bag->set('keywords', ['PHP', 'Framework', 'CopperPHP', 'Web Development']);
    $appConfig->timezone = false;
    $appConfig->dateFormat = 'Y-m-d';
    $appConfig->timeFormat = 'H:i:s';
    $appConfig->dateTimeFormat = 'Y-m-d H:i:s';

    $appConfig->serialize_precision = -1;
};