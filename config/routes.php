<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use Copper\Controller\AbstractController;
use Copper\Controller\RedirectController;

return function (RoutingConfigurator $routes) {
    // redirect URLs with a trailing slash to the same URL without a trailing slash (for example /en/blog/ to /en/blog).
    $routes->add('remove_trailing_slash', '/{url}')
        ->controller([RedirectController::class, 'removeTrailingSlashAction'])
        ->requirements(['url' => '.*/$']);

    // Default index page
    $routes->add('index', '/')
        ->controller([AbstractController::class, 'render'])
        ->defaults(['view' => 'index'])
        ->methods(['GET']);
};