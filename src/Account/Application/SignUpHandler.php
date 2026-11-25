<?php

declare(strict_types=1);

namespace App\Account\Application;

use App\Account\Domain\Accounts;
use Phil\DddBundle\Application\Bus\CommandHandler;

/**
 * Handles SignUpCommand: the account is registered through the domain's port, in the transaction of the command bus.
 *
 * @see SignUpHandlerTest
 */
final readonly class SignUpHandler implements CommandHandler
{
    public function __construct(
        private Accounts $accounts,
    ) {
    }

    public function __invoke(SignUpCommand $command): void
    {
        $this->accounts->register($command->username, $command->email, $command->plainPassword);
    }
}
