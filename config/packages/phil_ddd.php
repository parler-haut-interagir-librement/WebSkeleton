<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * phil/ddd-bundle: the buses its CommandBus and QueryBus dispatch to, declared in messenger.php.
 */
return App::config([
    'phil_ddd' => [
        'command_bus' => 'command.bus',
        'query_bus' => 'query.bus',
    ],
]);
