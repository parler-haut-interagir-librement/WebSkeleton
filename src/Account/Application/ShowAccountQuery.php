<?php

declare(strict_types=1);

namespace App\Account\Application;

use App\Account\Domain\AccountId;
use Phil\DddBundle\Application\Bus\Query;

/**
 * Asks for an account by its identifier, through the query bus: its view, or null when no account has it.
 *
 * @see ShowAccountHandler
 *
 * @implements Query<AccountView|null>
 */
final readonly class ShowAccountQuery implements Query
{
    public function __construct(
        public AccountId $id,
    ) {
    }
}
