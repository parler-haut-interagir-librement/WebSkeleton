<?php

declare(strict_types=1);

namespace App\Domain\Text;

/**
 * The port that turns a title into a slug; the infrastructure gives its adapter.
 */
interface TitleSlugger
{
    /**
     * @param non-empty-string $title
     */
    public function slugify(string $title): Slug;
}
