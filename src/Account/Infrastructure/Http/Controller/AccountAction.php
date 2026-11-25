<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Http\Controller;

use App\Account\Domain\Role;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The page of the signed-in member, /fr/account and /en/account: its account, its privacy choices and the button that
 * signs it out.
 *
 * @see AccountActionTest
 */
#[IsGranted(Role::USER)]
final class AccountAction extends AbstractController
{
    public const string ROUTE = 'account_show';

    #[Route(path: '/account', name: self::ROUTE, methods: ['GET'])]
    public function __invoke(#[CurrentUser] User $user): Response
    {
        return $this->render('account/account.html.twig', ['user' => $user]);
    }
}
