<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * The routes of PhilWellKnownBundle: the documents of /.well-known/.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@PhilWellKnownBundle/config/routes.php');
};
