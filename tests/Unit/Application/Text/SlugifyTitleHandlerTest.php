<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Text;

use App\Application\Text\SlugifyTitleHandler;
use App\Application\Text\SlugifyTitleQuery;
use App\Domain\Text\Slug;
use App\Domain\Text\TitleSlugger;
use LogicException;
use Override;
use Phil\DddBundle\Domain\Exception\InvalidValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SlugifyTitleHandler::class)]
#[CoversClass(SlugifyTitleQuery::class)]
final class SlugifyTitleHandlerTest extends TestCase
{
    public function testItAsksThePortWithTheTrimmedTitle(): void
    {
        $slugger = new class implements TitleSlugger {
            public ?string $title = null;

            #[Override]
            public function slugify(string $title): Slug
            {
                $this->title = $title;

                return new Slug('a-title');
            }
        };

        $slug = new SlugifyTitleHandler($slugger)(new SlugifyTitleQuery("  A title \n"));

        self::assertSame('a-title', $slug->value);
        self::assertSame('A title', $slugger->title);
    }

    public function testItRefusesABlankTitle(): void
    {
        $slugger = new class implements TitleSlugger {
            #[Override]
            public function slugify(string $title): Slug
            {
                throw new LogicException('The port is not asked for a blank title.');
            }
        };

        try {
            new SlugifyTitleHandler($slugger)(new SlugifyTitleQuery(" \t "));
            self::fail('A slug was made of a blank title.');
        } catch (InvalidValue $invalidValue) {
            self::assertSame('A title is required.', $invalidValue->getMessage());
        }
    }
}
