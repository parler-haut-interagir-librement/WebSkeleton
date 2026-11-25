<?php

declare(strict_types=1);

namespace App\Account\Domain;

/**
 * The accounts of the site, the port the application registers a new member through; the infrastructure hashes the
 * password and keeps the account.
 */
interface Accounts
{
    public function register(string $username, string $email, string $plainPassword): void;
}
