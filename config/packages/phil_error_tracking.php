<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The errors of the site, kept in its own database by phil/error-tracking-bundle: the exceptions of the requests, of
 * the messages and of the commands, grouped, with their personal data scrubbed. The administration shows them under
 * /admin/errors, in the dashboard. Alerts without a chatter transport are logged; they are sent in the request, until a
 * transport routes them.
 *
 * @see vendor/phil/error-tracking-bundle/doc/configuration.md
 */
return App::config([
    'phil_error_tracking' => [
        'release' => '%env(default::APP_RELEASE)%',
        'alerts' => [
            'async' => false,
        ],
        // A check of phil/monitoring-bundle, which the site does not register.
        'health_check' => false,
        'admin' => [
            'enabled' => true,
        ],
    ],
    'when@prod' => [
        'phil_error_tracking' => [
            // The template runs on one server, like the lock and cache.app: a site on several gives the rate limit a
            // shared pool and removes this line, as for phil/browser-security-bundle.
            'rate_limit' => [
                'single_server' => true,
            ],
        ],
    ],
]);
