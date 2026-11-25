<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * The routes of PhilSitemapBundle: /sitemap.xml and its files.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@PhilSitemapBundle/config/routes.php');
};
