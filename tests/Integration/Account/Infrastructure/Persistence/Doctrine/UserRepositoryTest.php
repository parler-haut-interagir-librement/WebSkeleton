<?php

declare(strict_types=1);

namespace App\Tests\Integration\Account\Infrastructure\Persistence\Doctrine;

use App\Account\Domain\AccountId;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use App\Account\Infrastructure\Persistence\Doctrine\UserRepository;
use App\Fixtures\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAnAccountIsFoundByItsAccountIdOrItsString(): void
    {
        $id = UserFactory::createOne(['username' => 'ada'])->getId();
        UserFactory::createOne(['username' => 'grace']);
        $repository = $this->repository();

        self::assertSame('ada', $repository->find($id)?->getUserIdentifier());
        self::assertSame('ada', $repository->find($id->toString())?->getUserIdentifier());
    }

    public function testAnyOtherStringFindsNothing(): void
    {
        UserFactory::createOne();
        $repository = $this->repository();

        self::assertNull($repository->find('not-a-uuid'));
        self::assertNull($repository->find('0196b6a0-0000-4000-8000-000000000000'));
        self::assertNull($repository->find(AccountId::generate()->toString()));
    }

    private function repository(): UserRepository
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $repository = $entityManager->getRepository(User::class);
        self::assertInstanceOf(UserRepository::class, $repository);

        return $repository;
    }
}
