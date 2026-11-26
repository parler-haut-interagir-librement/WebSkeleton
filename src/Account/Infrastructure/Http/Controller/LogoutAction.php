<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Http\Controller;

use LogicException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The sign-out, /fr/logout and /en/logout, by POST only and with the CSRF token "logout": the firewall answers it
 * before this action is reached.
 *
 * @see config/packages/security.yaml
 */
#[AsController]
final readonly class LogoutAction
{
    public const string ROUTE = 'account_logout';

    #[Route(path: '/logout', name: self::ROUTE, methods: ['POST'])]
    public function __invoke(): never
    {
        throw new LogicException('The logout of the firewall answers this route.');
    }
}
