<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Persistence\Doctrine;

use App\Account\Domain\Accounts;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use SensitiveParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * The accounts in the database, through Doctrine: a new account gets its password hashed and is persisted; the
 * transaction of the command bus flushes it.
 *
 * @see DoctrineAccountsTest
 */
final readonly class DoctrineAccounts implements Accounts
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Override]
    public function register(string $username, string $email, #[SensitiveParameter] string $plainPassword): void
    {
        $user = new User($username, $email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
    }
}
