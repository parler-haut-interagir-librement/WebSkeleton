<?php

declare(strict_types=1);

namespace App\Account\Domain;

use Phil\DddBundle\Domain\Identifier\Identifier;

/**
 * The identifier of an account: a UUIDv7, given to a new account by AccountId::generate(). It is also the identifier
 * phil/privacy-bundle knows the member by, never their email address.
 */
final readonly class AccountId extends Identifier
{
}
