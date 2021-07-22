<?php

use Copper\Component\Auth\AbstractUserEntity;
use Copper\Component\Auth\AuthConfigurator;
use Copper\Kernel;

return function (AuthConfigurator $auth) {

    $auth->defaultAccessRole = false;

    $auth->loginRoute = 'get_auth_login';
    $auth->returnToRouteParam = 'return_to_route';
    $auth->forbiddenTemplate = 'forbidden';
    $auth->log = true;
    $auth->log_filepath = Kernel::getAppLogPath('auth.log');
    $auth->log_format = '[%1$s] %2$s - %3$s (%4$s)';

    $auth->serviceClassName = null;
};