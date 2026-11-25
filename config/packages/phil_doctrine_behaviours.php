<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The behaviours of phil/doctrine-behaviours-bundle: the timestamps of the accounts only. Each other behaviour is
 * turned on here when an entity needs it.
 *
 * @see vendor/phil/doctrine-behaviours-bundle/doc/configuration.md
 */
return App::config([
    'phil_doctrine_behaviours' => [
        'timestamps' => true,
        'blame' => false,
        'soft_delete' => false,
        'tree' => false,
        'deactivation' => false,
        'position' => false,
        'translation' => false,
        'privacy' => false,
    ],
]);
