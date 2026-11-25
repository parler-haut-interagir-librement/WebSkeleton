<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Privacy;

use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Phil\PrivacyBundle\Application\Contract\PersonalDataEraser;
use Phil\PrivacyBundle\Application\Contract\PersonalDataExporter;
use Phil\PrivacyBundle\Domain\Retention\ErasureEffect;
use Phil\PrivacyBundle\Domain\Subject\DataSubject;
use Phil\PrivacyBundle\Domain\Subject\ErasureReport;
use Phil\PrivacyBundle\Domain\Subject\ModuleName;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

/**
 * The account in the export and the erasure of phil/privacy-bundle, as the module "account": the export gives what the
 * account holds, the erasure deletes it. The member is known by the UUID of their account.
 *
 * @see AccountPersonalDataTest
 */
final readonly class AccountPersonalData implements PersonalDataExporter, PersonalDataEraser
{
    public const string MODULE = 'account';

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Override]
    public function module(): ModuleName
    {
        return ModuleName::fromString(self::MODULE);
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function export(DataSubject $subject): array
    {
        $user = Uuid::isValid($subject->value) ? $this->entityManager->find(User::class, Uuid::fromString($subject->value)) : null;
        if (!$user instanceof User) {
            return [];
        }

        return ['account' => [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'created_at' => $user->createdAt()?->format(DateTimeInterface::ATOM),
            'updated_at' => $user->updatedAt()?->format(DateTimeInterface::ATOM),
        ]];
    }

    /**
     * Deletes the account, idempotent: a second run finds nothing more to delete.
     */
    #[Override]
    public function erase(DataSubject $subject): ErasureReport
    {
        if (Uuid::isValid($subject->value)) {
            $this->entityManager->createQueryBuilder()
                ->delete(User::class, 'u')
                ->where('u.id = :id')
                ->setParameter('id', Uuid::fromString($subject->value), UuidType::NAME)
                ->getQuery()
                ->execute();
        }

        return ErasureReport::of(self::MODULE, ['user' => ErasureEffect::Deleted]);
    }
}
