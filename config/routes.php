<?php

use Copper\Component\CP\CPController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use Copper\Controller\AbstractController;
use Copper\Controller\RedirectController;

const ROUTE_index = 'index';
const ROUTE_error = 'error';
const ROUTE_get_copper_cp = 'get_copper_cp';
const ROUTE_copper_cp_action = 'copper_cp_action';

return function (RoutingConfigurator $routes) {
    // redirect URLs with a trailing slash to the same URL without a trailing slash (for example /en/blog/ to /en/blog).
    $routes->add('remove_trailing_slash', '/{url}')
        ->controller([RedirectController::class, 'removeTrailingSlashAction'])
        ->requirements(['url' => '.*/$']);

    // Copper Control Panel
    $routes->add(ROUTE_get_copper_cp, '/copper_cp')
        ->controller([CPController::class, 'getIndex'])
        ->methods(['GET']);

    // Copper Control Panel
    $routes->add(ROUTE_copper_cp_action, '/copper_cp/{action}')
        ->controller([CPController::class, 'postAction'])
        ->methods(['GET', 'POST']);

    // Default index page
    $routes->add(ROUTE_index, '/')
        ->controller([AbstractController::class, 'viewResponse'])
        ->defaults(['view' => 'index'])
        ->methods(['GET']);

    // Default error page
    $routes->add(ROUTE_error, '/error')
        ->controller([AbstractController::class, 'viewError'])
        ->methods(['GET']);
};