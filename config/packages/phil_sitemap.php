<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * phil/sitemap-bundle: /sitemap.xml, from the routes whose options carry "sitemap", in every language of the site.
 *
 * @see https://github.com/parler-haut-interagir-librement/sitemap-bundle
 */
return App::config([
    'phil_sitemap' => [
        'default_section' => 'main',
    ],
    'when@dev' => [
        'phil_sitemap' => [
            'allow_localhost' => true,
        ],
    ],
    'when@test' => [
        'phil_sitemap' => [
            'allow_localhost' => true,
        ],
    ],
]);
