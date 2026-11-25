<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * The routes of PhilAccessibilityStatementBundle: the accessibility statement, /fr/accessibilite and /en/accessibility.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@PhilAccessibilityStatementBundle/config/routes.php')
        ->prefix('/{_locale}')
        ->requirements(['_locale' => '%app.locale_requirement%']);
};
