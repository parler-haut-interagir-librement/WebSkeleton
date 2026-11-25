<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The assets of the application, phil-ux, the stylesheet of phil/ux-css, under phil-ux/, and the Stimulus controller of
 * phil/spam-protection-bundle.
 *
 * @see https://symfony.com/doc/current/frontend/asset_mapper.html
 */
return App::config([
    'framework' => [
        'asset_mapper' => [
            'paths' => [
                'assets/' => '',
                'vendor/phil/ux-css/dist' => 'phil-ux',
                // The Stimulus controller of the proof of work that protects the contact form.
                'vendor/phil/spam-protection-bundle/assets' => '@phil/spam-protection-bundle',
            ],
            'missing_import_mode' => 'strict',
        ],
    ],
    'when@prod' => [
        'framework' => [
            'asset_mapper' => [
                'missing_import_mode' => 'warn',
            ],
        ],
    ],
]);
