<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The lock store; flock on one server, a store every server shares (PostgreSQL advisory locks, Redis) on several.
 */
return App::config([
    'framework' => [
        'lock' => '%env(LOCK_DSN)%',
    ],
]);
