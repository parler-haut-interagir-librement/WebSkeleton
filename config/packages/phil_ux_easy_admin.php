<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The PHIL UX theme of the EasyAdmin administration, linked on every page of every dashboard.
 *
 * @see vendor/phil/ux-easyadmin-bundle/doc/installation.md
 */
return App::config([
    'phil_ux_easy_admin' => [
        'theme' => true,
    ],
]);
