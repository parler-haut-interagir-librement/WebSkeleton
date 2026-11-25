<?php

declare(strict_types=1);

namespace App\Tests\Unit\Text\Domain;

use App\Text\Domain\Slug;
use Phil\DddBundle\Domain\Exception\InvalidValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Slug::class)]
final class SlugTest extends TestCase
{
    #[DataProvider('provideValidSlugs')]
    public function testItKeepsAValidSlug(string $value): void
    {
        self::assertSame($value, new Slug($value)->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideValidSlugs(): iterable
    {
        yield 'one letter' => ['a'];
        yield 'one digit' => ['7'];
        yield 'words' => ['hello-world-2026'];
        yield 'the longest' => [str_repeat('a', Slug::MAX_LENGTH)];
    }

    #[DataProvider('provideInvalidSlugs')]
    public function testItRefusesAnInvalidSlug(string $value, string $reason): void
    {
        try {
            new Slug($value);
            self::fail('The slug was built.');
        } catch (InvalidValue $invalidValue) {
            self::assertSame($reason, $invalidValue->getMessage());
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideInvalidSlugs(): iterable
    {
        $shape = 'A slug holds lowercase letters and digits, in words joined by single hyphens.';

        yield 'empty' => ['', 'A slug cannot be empty.'];
        yield 'too long' => [str_repeat('a', Slug::MAX_LENGTH + 1), 'A slug has 200 characters at most.'];
        yield 'uppercase' => ['Hello', $shape];
        yield 'leading hyphen' => ['-hello', $shape];
        yield 'trailing hyphen' => ['hello-', $shape];
        yield 'double hyphen' => ['hello--world', $shape];
        yield 'space' => ['hello world', $shape];
        yield 'accent' => ['été', $shape];
        yield 'trailing newline' => ["hello\n", $shape];
    }

    public function testEquality(): void
    {
        self::assertTrue(new Slug('a-b')->equals(new Slug('a-b')));
        self::assertFalse(new Slug('a-b')->equals(new Slug('a-c')));
    }
}
