<?php

declare(strict_types=1);

namespace App\Account\Application;

use Phil\DddBundle\Application\Bus\Command;
use SensitiveParameter;

/**
 * Signs a new member up, through the command bus: its user name, its email address and its password, still plain.
 */
final readonly class SignUpCommand implements Command
{
    public function __construct(
        public string $username,
        public string $email,
        #[SensitiveParameter]
        public string $plainPassword,
    ) {
    }
}
