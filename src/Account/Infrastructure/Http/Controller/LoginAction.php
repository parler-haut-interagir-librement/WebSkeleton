<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * The sign-in page, /fr/login and /en/login. The firewall reads the form it posts: this action only shows it, with the
 * last user name tried and the error of the last attempt.
 *
 * @see config/packages/security.yaml
 * @see LoginActionTest
 */
final class LoginAction extends AbstractController
{
    public const string ROUTE = 'account_login';

    #[Route(path: '/login', name: self::ROUTE, methods: ['GET', 'POST'])]
    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute(AccountAction::ROUTE);
        }

        return $this->render('account/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
