<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Text;

use App\Domain\Text\Slug;
use App\Infrastructure\Text\AsciiTitleSlugger;
use Phil\DddBundle\Domain\Exception\InvalidValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[CoversClass(AsciiTitleSlugger::class)]
final class AsciiTitleSluggerTest extends TestCase
{
    /**
     * @param non-empty-string $title
     */
    #[DataProvider('provideTitles')]
    public function testItMakesASlug(string $title, string $expected): void
    {
        self::assertSame($expected, new AsciiTitleSlugger(new AsciiSlugger())->slugify($title)->value);
    }

    /**
     * @return iterable<string, array{non-empty-string, string}>
     */
    public static function provideTitles(): iterable
    {
        yield 'words' => ['This IS the _-! Web SKELETON  project', 'this-is-the-web-skeleton-project'];
        yield 'accents' => ['Été à Montréal', 'ete-a-montreal'];
        yield 'cut at the length of a slug' => [str_repeat('a', Slug::MAX_LENGTH + 10), str_repeat('a', Slug::MAX_LENGTH)];
        yield 'no hyphen left at the cut' => [str_repeat('a', Slug::MAX_LENGTH - 1).' b', str_repeat('a', Slug::MAX_LENGTH - 1)];
    }

    public function testATitleWithoutLetterOrDigitHasNoSlug(): void
    {
        $this->expectException(InvalidValue::class);

        new AsciiTitleSlugger(new AsciiSlugger())->slugify('!!!');
    }
}
