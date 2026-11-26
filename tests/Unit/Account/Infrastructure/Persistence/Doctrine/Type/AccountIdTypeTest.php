<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Infrastructure\Persistence\Doctrine\Type;

use App\Account\Domain\AccountId;
use App\Account\Infrastructure\Persistence\Doctrine\Type\AccountIdType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccountIdType::class)]
final class AccountIdTypeTest extends TestCase
{
    public function testAColumnReadsBackAnAccountId(): void
    {
        $type = new AccountIdType();
        $id = AccountId::generate();

        $postgres = new PostgreSQLPlatform();
        $stored = $type->convertToDatabaseValue($id, $postgres);
        self::assertSame($id->toString(), $stored);
        $read = $type->convertToPHPValue($stored, $postgres);
        self::assertInstanceOf(AccountId::class, $read);
        self::assertTrue($read->equals($id));

        $sqlite = new SQLitePlatform();
        $bytes = $type->convertToDatabaseValue($id, $sqlite);
        self::assertSame(16, \strlen((string) $bytes));
        $readFromBytes = $type->convertToPHPValue($bytes, $sqlite);
        self::assertInstanceOf(AccountId::class, $readFromBytes);
        self::assertTrue($readFromBytes->equals($id));
        self::assertSame('account_id', AccountIdType::NAME);
    }

    public function testAStringIsRefused(): void
    {
        $this->expectException(InvalidType::class);

        new AccountIdType()->convertToDatabaseValue(AccountId::generate()->toString(), new PostgreSQLPlatform());
    }
}
