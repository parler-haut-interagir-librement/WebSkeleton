<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * Twig components: the recipes of the PHIL UX kit copied in templates/components/ by the Symfony UX Toolkit.
 *
 * @see https://symfony.com/bundles/ux-twig-component/current/index.html
 */
return App::config([
    'twig_component' => [
        'anonymous_template_directory' => 'components/',
        'defaults' => [
            'App\Shared\Infrastructure\Twig\Component\\' => 'components/',
        ],
    ],
]);
