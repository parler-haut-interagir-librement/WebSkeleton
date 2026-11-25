<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Form;

use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * What a new member gives to sign up; the user name and the email address are checked against the existing accounts.
 *
 * @see SignupForm
 */
#[UniqueEntity(fields: ['username'], message: 'signup.username.taken', entityClass: User::class, identifierFieldNames: ['id'])]
#[UniqueEntity(fields: ['email'], message: 'signup.email.taken', entityClass: User::class, identifierFieldNames: ['id'])]
final class SignupFormData
{
    public const int PASSWORD_MIN_LENGTH = 12;

    public ?string $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex(pattern: '/^[\p{L}\p{N}._-]+$/u', message: 'signup.username.characters')]
    public ?string $username = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null {
        // Normalized as the account keeps it, so that the uniqueness check sees the address the account will have.
        set(?string $value) => null === $value ? null : User::normalizeEmail($value);
    }

    #[Assert\NotBlank]
    #[Assert\Length(min: self::PASSWORD_MIN_LENGTH, max: 4096)]
    public ?string $plainPassword = null;
}
