<?php

use Copper\Component\Auth\AbstractUserEntity;
use Copper\Component\Auth\AuthConfigurator;

return function (AuthConfigurator $auth) {

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