<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Every page lives under the prefix of its language, /fr or /en; "/" sends the visitor to the language they prefer.
 * The languages are the parameter app.locales of config/services.php. The API and the files of robots and
 * search engines stay outside the prefixes.
 *
 * Each context of src/ puts its controllers in Infrastructure/Http/Controller, Api or Root: the globs find them.
 */
return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('../src/*/Infrastructure/Http/Controller/**/*.php', 'attribute')
        ->prefix('/{_locale}', false)
        ->requirements(['_locale' => '%app.locale_requirement%']);

    $routingConfigurator->import('../src/*/Infrastructure/Http/Api/**/*.php', 'attribute')
        ->prefix('/api');

    $routingConfigurator->import('../src/*/Infrastructure/Http/Root/**/*.php', 'attribute');
};
