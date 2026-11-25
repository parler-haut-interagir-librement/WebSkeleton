<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * phil/ux-kit: the hosts whose images a page may show; the site's own images need none.
 */
return App::config([
    'phil_ux_kit' => [
        'image' => [
            'hosts' => [],
        ],
    ],
]);
