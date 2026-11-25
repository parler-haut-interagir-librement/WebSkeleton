<?php

declare(strict_types=1);

namespace App\Infrastructure\Text;

use App\Domain\Text\Slug;
use App\Domain\Text\TitleSlugger;
use Override;
use Symfony\Component\String\Slugger\SluggerInterface;

use function Symfony\Component\String\u;

/**
 * The adapter of TitleSlugger on Symfony's slugger: transliterated to ASCII, lowercase, cut to the length of a slug.
 *
 * @see AsciiTitleSluggerTest
 */
final readonly class AsciiTitleSlugger implements TitleSlugger
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    #[Override]
    public function slugify(string $title): Slug
    {
        $slug = $this->slugger->slug($title)->lower()->truncate(Slug::MAX_LENGTH);

        return new Slug(u($slug->toString())->trimEnd('-')->toString());
    }
}
