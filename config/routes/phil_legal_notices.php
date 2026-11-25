<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * The routes of PhilLegalNoticesBundle: the legal notice and the privacy policy, /fr/legal and /en/legal, /fr/privacy and /en/privacy.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@PhilLegalNoticesBundle/config/routes.php')
        ->prefix('/{_locale}')
        ->requirements(['_locale' => '%app.locale_requirement%']);
};
