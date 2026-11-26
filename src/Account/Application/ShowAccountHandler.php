<?php

declare(strict_types=1);

namespace App\Account\Application;

use Phil\DddBundle\Application\Bus\QueryHandler;

/**
 * Answers ShowAccountQuery: the view of the account, read through the port of the application without loading the
 * entity of the infrastructure.
 *
 * @see ShowAccountHandlerTest
 */
final readonly class ShowAccountHandler implements QueryHandler
{
    public function __construct(
        private AccountViews $accountViews,
    ) {
    }

    public function __invoke(ShowAccountQuery $query): ?AccountView
    {
        return $this->accountViews->find($query->id);
    }
}
