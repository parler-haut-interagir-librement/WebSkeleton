<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Persistence\Doctrine;

use App\Account\Domain\AccountId;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use Phil\DddBundle\Domain\Exception\InvalidValue;

/**
 * The repository of the accounts. The DBAL type AccountIdType takes an AccountId only, never a string: find() also
 * accepts the canonical string of an AccountId, as the administration passes it from its URLs, and finds nothing for
 * any other string.
 *
 * @extends ServiceEntityRepository<User>
 *
 * @see UserRepositoryTest
 */
final class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    #[Override]
    public function find(mixed $id, LockMode|int|null $lockMode = LockMode::NONE, ?int $lockVersion = null): ?User
    {
        if (\is_string($id)) {
            try {
                $id = AccountId::fromString($id);
            } catch (InvalidValue) {
                return null;
            }
        }

        return parent::find($id, $lockMode, $lockVersion);
    }
}
