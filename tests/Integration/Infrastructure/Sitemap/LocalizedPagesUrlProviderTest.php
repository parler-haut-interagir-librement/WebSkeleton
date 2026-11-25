<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Sitemap;

use App\Infrastructure\Sitemap\LocalizedPagesUrlProvider;
use Override;
use Phil\Sitemap\Alternate;
use Phil\Sitemap\Url;
use Phil\Sitemap\UrlContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(LocalizedPagesUrlProvider::class)]
final class LocalizedPagesUrlProviderTest extends KernelTestCase
{
    public function testEachPageIsListedOncePerLanguageWithItsAlternates(): void
    {
        $urls = $this->provide(null);

        self::assertSame([
            'main http://localhost/fr 1',
            'main http://localhost/en 1',
            'main http://localhost/fr/contact 0.5',
            'main http://localhost/en/contact 0.5',
        ], array_map(static fn (array $entry): string => $entry[1].' '.$entry[0]->loc.' '.$entry[0]->priority, $urls));

        self::assertSame(
            ['fr http://localhost/fr', 'en http://localhost/en', 'x-default http://localhost/fr'],
            array_map(static fn (Alternate $alternate): string => $alternate->hreflang.' '.$alternate->href, $urls[1][0]->alternates),
        );
    }

    public function testItAddsItsPagesToItsOwnSectionOnly(): void
    {
        self::assertCount(4, $this->provide(LocalizedPagesUrlProvider::SECTION));
        self::assertSame([], $this->provide('news'));
    }

    /**
     * @return list<array{Url, string}>
     */
    private function provide(?string $section): array
    {
        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        self::assertInstanceOf(UrlGeneratorInterface::class, $urlGenerator);

        $container = new class implements UrlContainer {
            /** @var list<array{Url, string}> */
            public array $urls = [];

            #[Override]
            public function add(Url $url, string $section): void
            {
                $this->urls[] = [$url, $section];
            }
        };

        new LocalizedPagesUrlProvider($urlGenerator, ['fr', 'en'], true)->provide($container, $section);

        return $container->urls;
    }
}
