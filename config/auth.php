<?php

use Copper\Component\Auth\AbstractUserEntity;
use Copper\Component\Auth\AuthConfigurator;
use Copper\Kernel;

return function (AuthConfigurator $auth) {

    $auth->loginRoute = 'get_auth_login';
    $auth->returnToRouteParam = 'return_to_route';
    $auth->forbiddenTemplate = 'forbidden';
    $auth->log = true;
    $auth->log_filepath = Kernel::getAppLogPath('auth.log');
    $auth->log_format = '[%1$s] %2$s - %3$s (%4$s)';

    $adminLogin = 'root';
    $adminPassword = 'pass';

    $adminUser = AbstractUserEntity::fromArray([
        "id" => 1,
        "login" => $adminLogin,
        "role" => AbstractUserEntity::ROLE_ADMIN
    ]);

    $auth->registerUserHandlerClosure(function ($id) use ($adminUser) {
        return ($id === 1) ? $adminUser : null;
    });

    $auth->registerValidateHandlerClosure(function ($login, $password) use ($adminUser, $adminPassword) {
        return ($login === $adminUser->login && $password === $adminPassword) ? $adminUser : null;
    });

};