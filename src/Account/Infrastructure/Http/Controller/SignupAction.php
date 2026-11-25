<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Http\Controller;

use App\Account\Application\SignUpCommand;
use App\Account\Infrastructure\Form\SignupForm;
use App\Account\Infrastructure\Form\SignupFormData;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Phil\DddBundle\Application\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The sign-up page, /fr/signup and /en/signup: a valid form signs the member up through the command bus, then signs it
 * in.
 *
 * @see SignupActionTest
 */
final class SignupAction extends AbstractController
{
    public const string ROUTE = 'account_signup';

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    #[Route(path: '/signup', name: self::ROUTE, methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute(AccountAction::ROUTE);
        }

        $data = new SignupFormData();
        $form = $this->createForm(SignupForm::class, $data)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = (string) $data->username;
            $this->commandBus->dispatch(new SignUpCommand($username, (string) $data->email, (string) $data->plainPassword));
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

            return ($user instanceof User ? $this->security->login($user, 'form_login', 'main') : null)
                ?? $this->redirectToRoute(AccountAction::ROUTE);
        }

        return $this->render('account/signup.html.twig', [
            'form' => $form,
            'password_min_length' => SignupFormData::PASSWORD_MIN_LENGTH,
        ]);
    }
}
