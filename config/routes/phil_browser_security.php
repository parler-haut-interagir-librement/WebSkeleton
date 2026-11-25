<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * The routes of PhilBrowserSecurityBundle: POST /_csp/report, where browsers send the violations of the Content Security Policy.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@PhilBrowserSecurityBundle/config/routes.php');
};
