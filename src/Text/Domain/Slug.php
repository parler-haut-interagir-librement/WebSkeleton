<?php

declare(strict_types=1);

namespace App\Text\Domain;

use Phil\DddBundle\Domain\Exception\InvalidValue;

/**
 * A slug: lowercase ASCII letters and digits, in words joined by single hyphens, 1 to 200 characters.
 *
 * A value object of the domain: plain PHP, valid from its construction or never built.
 *
 * @see SlugTest
 */
final readonly class Slug
{
    public const int MAX_LENGTH = 200;

    private const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D';

    public function __construct(
        public string $value,
    ) {
        if ('' === $value) {
            throw InvalidValue::because('A slug cannot be empty.');
        }

        if (\strlen($value) > self::MAX_LENGTH) {
            throw InvalidValue::because(\sprintf('A slug has %d characters at most.', self::MAX_LENGTH));
        }

        if (1 !== preg_match(self::PATTERN, $value)) {
            throw InvalidValue::because('A slug holds lowercase letters and digits, in words joined by single hyphens.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
