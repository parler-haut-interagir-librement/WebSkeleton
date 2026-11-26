<?php

declare(strict_types=1);

namespace App\Account\Application;

use App\Account\Domain\AccountId;
use DateTimeImmutable;

/**
 * An account as the administration reads it: its identifier, its user name, its roles and when it was created. The
 * Serializer writes the identifier as its canonical string and builds it back, through phil/ddd-bundle; the email
 * address and the password stay out.
 */
final readonly class AccountView
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public AccountId $id,
        public string $username,
        public array $roles,
        public ?DateTimeImmutable $createdAt,
    ) {
    }
}
