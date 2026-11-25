<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The two buses of phil/ddd-bundle: commands go through a transaction, queries do not. Both are synchronous; a
 * transport is routed here once a message must run later. Messages carry identifiers only.
 *
 * @see https://symfony.com/doc/current/messenger.html
 */
return App::config([
    'framework' => [
        'messenger' => [
            'default_bus' => 'command.bus',
            'buses' => [
                'command.bus' => [
                    'middleware' => ['validation', 'doctrine_transaction'],
                ],
                'query.bus' => null,
            ],
            'transports' => [
                'sync' => 'sync://',
            ],
        ],
    ],
]);
