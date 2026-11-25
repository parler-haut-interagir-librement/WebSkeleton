<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * The pages of PhilPrivacyBundle under the language prefix: the privacy choices of a member, /fr/privacy-choices and
 * /en/privacy-choices, the consent and the cookie policy.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@PhilPrivacyBundle/config/routes.php')
        ->prefix('/{_locale}/privacy-choices', false)
        ->requirements(['_locale' => '%app.locale_requirement%']);
};
