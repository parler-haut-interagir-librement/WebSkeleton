<?php

declare(strict_types=1);

namespace App\Application\Text;

use App\Domain\Text\Slug;
use Phil\DddBundle\Application\Bus\Query;

/**
 * Asks for the slug of a title, through the query bus.
 *
 * @implements Query<Slug>
 */
final readonly class SlugifyTitleQuery implements Query
{
    public function __construct(
        public string $title,
    ) {
    }
}
