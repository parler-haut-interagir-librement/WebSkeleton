<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The routes the route smoke test and the accessibility test request, through `Phil\DevTools\Testing\SmokeRoutes`:
 * every GET route without a parameter, and every route whose only parameter is the language, once per language of
 * `app.locales`.
 */
final class SiteRoutes
{
    /**
     * The prefixes of the route names left out of both tests: those of the profiler and the error pages, named `_…`.
     */
    public const array EXCLUDED = ['_'];

    /**
     * One set of route parameters per language of the site.
     *
     * @return list<array<string, string>>
     */
    public static function parameterSets(ContainerInterface $container): array
    {
        /** @var list<string> $locales */
        $locales = $container->getParameter('app.locales');

        return array_map(static fn (string $locale): array => ['_locale' => $locale], $locales);
    }
}
