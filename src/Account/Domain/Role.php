<?php

declare(strict_types=1);

namespace App\Account\Domain;

/**
 * The roles of an account: every member has ROLE_USER; ROLE_ADMIN, above it, opens the administration.
 */
final class Role
{
    public const string USER = 'ROLE_USER';

    public const string ADMIN = 'ROLE_ADMIN';
}
