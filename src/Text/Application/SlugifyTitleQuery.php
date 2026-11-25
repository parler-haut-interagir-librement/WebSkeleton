<?php

declare(strict_types=1);

namespace App\Text\Application;

use App\Text\Domain\Slug;
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
