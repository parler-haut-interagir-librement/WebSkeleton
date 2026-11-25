<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Account\Domain\Role;
use App\Account\Infrastructure\Http\Controller\AccountAction;
use App\Account\Infrastructure\Http\Controller\LoginAction;
use App\Account\Infrastructure\Http\Controller\LogoutAction;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * The member accounts: sign-in by form with a CSRF token, attempts throttled, sign-out by POST with a CSRF token, the
 * user checked again each time it is refreshed from the session. The pages live under the language prefix, /fr/login
 * and /en/login; the administration, under /admin, is for ROLE_ADMIN only.
 *
 * @see https://symfony.com/doc/current/security.html
 */
return App::config([
    'security' => [
        'password_hashers' => [
            PasswordAuthenticatedUserInterface::class => 'auto',
        ],
        'providers' => [
            'app_user_provider' => [
                'entity' => [
                    'class' => User::class,
                    'property' => 'username',
                ],
            ],
        ],
        'role_hierarchy' => [
            Role::ADMIN => [Role::USER],
        ],
        'firewalls' => [
            'dev' => [
                // The profiler, the debug toolbar and the assets are never behind the firewall.
                'pattern' => '^/(_profiler(/|$)|(_wdt|assets)/)',
                'security' => false,
            ],
            'main' => [
                'lazy' => true,
                'provider' => 'app_user_provider',
                // A disabled or changed account is signed out on its next request, not at the end of its session.
                'user_checker_on_refresh' => true,
                'form_login' => [
                    'login_path' => LoginAction::ROUTE,
                    'check_path' => LoginAction::ROUTE,
                    'enable_csrf' => true,
                    'csrf_token_id' => 'authenticate',
                    'default_target_path' => AccountAction::ROUTE,
                ],
                // The sudo mode: the deletion of an account by phil/privacy-bundle asks for the password again when
                // the session has not proved itself recently.
                're_authentication_entry_point' => 'form_login',
                'logout' => [
                    // A route of POST only, under the language prefix: a link or an image can never sign anyone out.
                    'path' => LogoutAction::ROUTE,
                    'enable_csrf' => true,
                    'csrf_token_id' => 'logout',
                ],
                // Five failed attempts a minute per user name and IP address, through symfony/rate-limiter.
                'login_throttling' => [
                    'max_attempts' => 5,
                ],
                // Impersonation stays off. A site that turns it on (https://symfony.com/doc/current/security/impersonating_user.html)
                // does it only by a POST request with a CSRF token, never by a query parameter.
            ],
        ],
        // Only the first rule that matches is applied.
        'access_control' => [
            ['path' => '^/admin', 'roles' => Role::ADMIN],
        ],
    ],
    'when@test' => [
        'security' => [
            'password_hashers' => [
                // The lowest work factors: secure hashes are not needed by the tests and slow them down.
                PasswordAuthenticatedUserInterface::class => [
                    'algorithm' => 'auto',
                    'cost' => 4,
                    'time_cost' => 3,
                    'memory_cost' => 10,
                ],
            ],
        ],
    ],
]);
