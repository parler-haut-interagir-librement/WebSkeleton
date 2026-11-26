<?php

declare(strict_types=1);

namespace App\Account\Application;

use App\Account\Domain\AccountId;

/**
 * The accounts as the queries read them, the port the infrastructure implements on the database.
 */
interface AccountViews
{
    public function find(AccountId $id): ?AccountView;
}
