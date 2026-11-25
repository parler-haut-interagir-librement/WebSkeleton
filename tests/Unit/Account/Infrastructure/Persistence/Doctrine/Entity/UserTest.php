<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Infrastructure\Persistence\Doctrine\Entity;

use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    public function testANewAccountHasAUuidAndANormalizedEmail(): void
    {
        $user = new User('ada', '  Ada@Example.COM ');

        self::assertInstanceOf(UuidV7::class, $user->getId());
        self::assertSame('ada', $user->getUsername());
        self::assertSame('ada', $user->getUserIdentifier());
        self::assertSame('ada', (string) $user);
        self::assertSame('ada@example.com', $user->getEmail());
        self::assertSame('ada@example.com', User::normalizeEmail(' ADA@example.com '));
        self::assertSame('', $user->getPassword());
        self::assertSame($user->getId()->toRfc4122(), $user->dataSubjectIdentifier());
        self::assertNotEquals(new User('ada', 'ada@example.com')->getId(), $user->getId());
    }

    public function testEveryAccountHasTheUserRoleOnce(): void
    {
        $user = new User('ada', 'ada@example.com');
        self::assertSame(['ROLE_USER'], $user->getRoles());

        $user->setRoles(['ROLE_ADMIN', 'ROLE_ADMIN', 'ROLE_EDITOR']);
        self::assertSame(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_EDITOR'], $user->getRoles());

        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        self::assertSame(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());
    }

    public function testTheSessionKeepsAHashOfThePasswordHash(): void
    {
        $user = new User('ada', 'ada@example.com');
        $user->setPassword('$argon2id$hash');

        $copy = new User('someone', 'someone@example.com');
        $copy->__unserialize($user->__serialize());

        self::assertSame(hash('crc32c', '$argon2id$hash'), $copy->getPassword());
        self::assertTrue($copy->getId()->equals($user->getId()));
        self::assertSame('ada', $copy->getUsername());
        self::assertSame('ada@example.com', $copy->getEmail());
        self::assertSame(
            [$user->getId()->toRfc4122(), 'ada', hash('crc32c', '$argon2id$hash'), 'ada@example.com'],
            $user->__serialize(),
        );
    }

    public function testTheTimestampsAreStamped(): void
    {
        $user = new User('ada', 'ada@example.com');
        $created = new DateTimeImmutable('2026-11-25 10:00:00');
        $user->stampCreated($created);

        self::assertSame($created, $user->createdAt());
        self::assertSame($created, $user->updatedAt());
    }

    public function testTheUserNameIsUniqueAndAsLongAsTheSignupAllows(): void
    {
        $attributes = new ReflectionProperty(User::class, 'username')->getAttributes(Column::class);
        self::assertCount(1, $attributes);
        $column = $attributes[0]->newInstance();

        self::assertSame(180, $column->length);
        self::assertTrue($column->unique);
    }

    public function testAnAccountWithoutUserNameHasNoIdentifier(): void
    {
        $this->expectException(LogicException::class);

        new User('', 'nobody@example.com')->getUserIdentifier();
    }
}
