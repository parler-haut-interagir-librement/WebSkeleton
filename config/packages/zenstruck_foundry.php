<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// A variable, not a constant: the file may be loaded twice in one process, by debug:config for example.
$foundry = [
    // See https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#full-default-bundle-configuration
    'zenstruck_foundry' => [
        'persistence' => [
            // Flush only once per call of `PersistentObjectFactory::create()`
            'flush_once' => true,
        ],
        'orm' => [
            'reset' => [
                // The tests reset the database with the migrations, as the site runs them.
                'mode' => 'migrate',
            ],
        ],
    ],
];

return App::config([
    'when@dev' => $foundry,
    'when@test' => $foundry,
]);
