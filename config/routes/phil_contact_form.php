<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * The routes of PhilContactFormBundle: the contact page, /fr/contact and /en/contact.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@PhilContactFormBundle/config/routes.php')
        ->prefix('/{_locale}/contact', false)
        ->requirements(['_locale' => '%app.locale_requirement%']);
};
