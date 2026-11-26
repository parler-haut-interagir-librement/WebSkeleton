<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Persistence\Doctrine\Type;

use App\Account\Domain\AccountId;
use Override;
use Phil\DddBundle\Infrastructure\Doctrine\IdentifierType;

/**
 * The DBAL type of AccountId, registered as "account_id" in config/packages/doctrine.yaml: a native UUID column on
 * PostgreSQL, 16 bytes on MySQL and SQLite, read back as an AccountId.
 *
 * @extends IdentifierType<AccountId>
 *
 * @see AccountIdTypeTest
 */
final class AccountIdType extends IdentifierType
{
    public const string NAME = 'account_id';

    #[Override]
    protected function identifierClass(): string
    {
        return AccountId::class;
    }
}
