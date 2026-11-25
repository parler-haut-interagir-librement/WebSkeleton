<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * phil/structured-data-bundle: the JSON-LD graph printed by structured_data() at the end of base.html.twig, in the
 * language of the page, with the script nonce of the Content Security Policy.
 *
 * @see https://github.com/parler-haut-interagir-librement/structured-data-bundle
 */
return App::config([
    'phil_structured_data' => [
        'request_language' => true,
        'website' => [
            'name' => '%brand%',
        ],
        'nonce' => [
            'content_security_policy' => true,
        ],
    ],
    'when@dev' => [
        'phil_structured_data' => [
            'allow_localhost' => true,
        ],
    ],
    'when@test' => [
        'phil_structured_data' => [
            'allow_localhost' => true,
        ],
    ],
]);
