<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * The routes of the EasyAdmin dashboards and of their CRUD controllers, under /admin, and the pages that
 * phil/error-tracking-bundle grafts on them, under /admin/errors.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('.', 'easyadmin.routes');
};
