<?php

use Copper\Component\CP\CPController;
use Copper\Component\Error\ErrorController;
use Copper\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use Copper\Controller\AbstractController;
use Copper\Controller\RedirectController;

const ROUTE_index = 'index';

const ROUTE_get_copper_cp = 'get_copper_cp';
const ROUTE_copper_cp_action = 'copper_cp_action';

return function (RoutingConfigurator $routes) {
    // redirect URLs with a trailing slash to the same URL without a trailing slash (for example /en/blog/ to /en/blog).
    $routes->add('remove_trailing_slash', '/{url}')
        ->controller([RedirectController::class, 'removeTrailingSlashAction'])
        ->requirements(['url' => '.*/$']);

    // Copper Control Panel
    $routes->add(Kernel::getCp()->config->route_name, Kernel::getCp()->config->route_path)
        ->controller([CPController::class, 'getIndex'])
        ->methods(['GET']);

    // Copper Control Panel
    $routes->add(Kernel::getCp()->config->action_route_name, Kernel::getCp()->config->route_path . '/{action}')
        ->controller([CPController::class, 'postAction'])
        ->methods(['GET', 'POST']);

    // Default index page
    $routes->add(ROUTE_index, '/')
        ->controller([AbstractController::class, 'viewResponse'])
        ->defaults(['view' => 'index'])
        ->methods(['GET']);

    // Default error page
    $routes->add(Kernel::getErrorHandler()->config->view_route_name, Kernel::getErrorHandler()->config->view_route_path)
        ->controller([ErrorController::class, 'viewErrorTemplate'])
        ->methods(['GET']);
};