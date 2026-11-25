<?php

declare(strict_types=1);

namespace App\Tests\Integration\Account\Infrastructure\Persistence\Doctrine;

use App\Account\Domain\Accounts;
use App\Account\Infrastructure\Persistence\Doctrine\DoctrineAccounts;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(DoctrineAccounts::class)]
final class DoctrineAccountsTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testANewAccountIsKeptWithItsPasswordHashed(): void
    {
        $accounts = self::getContainer()->get(Accounts::class);
        self::assertInstanceOf(DoctrineAccounts::class, $accounts);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $accounts->register('ada', 'Ada@Example.com', 'a long password');
        $entityManager->flush();
        $entityManager->clear();

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'ada']);
        self::assertInstanceOf(User::class, $user);
        self::assertSame('ada@example.com', $user->getEmail());
        self::assertNotSame('a long password', $user->getPassword());
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertInstanceOf(UserPasswordHasherInterface::class, $hasher);
        self::assertTrue($hasher->isPasswordValid($user, 'a long password'));
        self::assertNotNull($user->createdAt());
    }
}
