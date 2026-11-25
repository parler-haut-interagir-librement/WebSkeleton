<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The mailer of the contact form; MAILER_DSN chooses the transport, null://null sends nothing.
 */
return App::config([
    'framework' => [
        'mailer' => [
            'dsn' => '%env(MAILER_DSN)%',
        ],
    ],
]);
