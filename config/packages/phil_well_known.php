<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * phil/well-known-bundle: /.well-known/security.txt (RFC 9116). Replace the contact and push the expiry date back
 * before it passes, at most a year ahead.
 *
 * @see https://github.com/parler-haut-interagir-librement/well-known-bundle
 */
return App::config([
    'phil_well_known' => [
        'security_txt' => [
            'contact' => ['mailto:security@example.com'],
            'expires' => '2027-11-25T00:00:00Z',
            'preferred_languages' => ['fr', 'en'],
        ],
    ],
]);
