<?php

declare(strict_types=1);

namespace App\Application\Text;

use App\Domain\Text\Slug;
use App\Domain\Text\TitleSlugger;
use Phil\DddBundle\Application\Bus\QueryHandler;
use Phil\DddBundle\Domain\Exception\InvalidValue;

/**
 * Answers SlugifyTitleQuery: the title, trimmed, made a slug by the domain's port. A title without a letter or a
 * digit has no slug.
 *
 * @see SlugifyTitleHandlerTest
 */
final readonly class SlugifyTitleHandler implements QueryHandler
{
    public function __construct(
        private TitleSlugger $slugger,
    ) {
    }

    public function __invoke(SlugifyTitleQuery $query): Slug
    {
        $title = trim($query->title);
        if ('' === $title) {
            throw InvalidValue::because('A title is required.');
        }

        return $this->slugger->slugify($title);
    }
}
