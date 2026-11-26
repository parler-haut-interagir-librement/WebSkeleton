<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Persistence\Doctrine;

use App\Account\Application\AccountView;
use App\Account\Application\AccountViews;
use App\Account\Domain\AccountId;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Override;

/**
 * The views of the accounts, read from the database through Doctrine: the entity is found by its AccountId, which
 * the DBAL type AccountIdType converts, and mapped to the view the queries answer.
 *
 * @see DoctrineAccountViewsTest
 */
final readonly class DoctrineAccountViews implements AccountViews
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Override]
    public function find(AccountId $id): ?AccountView
    {
        $user = $this->entityManager->find(User::class, $id);
        if (!$user instanceof User) {
            return null;
        }

        return new AccountView($user->getId(), $user->getUsername(), $user->getRoles(), $user->createdAt());
    }
}
