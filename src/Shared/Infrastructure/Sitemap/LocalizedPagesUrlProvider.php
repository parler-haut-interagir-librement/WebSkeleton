<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Sitemap;

use App\Shared\Infrastructure\Http\Controller\HomeAction;
use Override;
use Phil\Sitemap\Alternate;
use Phil\Sitemap\Url;
use Phil\Sitemap\UrlContainer;
use Phil\Sitemap\UrlProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The pages of the site that live under the prefix of each language, for phil/sitemap-bundle: each page once per
 * language, with every language, and the default one as x-default, for alternates. Add a page to PAGES.
 *
 * @see LocalizedPagesUrlProviderTest
 */
final readonly class LocalizedPagesUrlProvider implements UrlProvider
{
    public const string SECTION = 'main';

    /**
     * The routes, by name, and their priority.
     */
    private const array PAGES = [
        HomeAction::class => 1.0,
        'phil_contact_form' => 0.5,
    ];

    /**
     * @param list<string> $locales the languages of the site, the default one first
     */
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private array $locales,
        #[Autowire(param: 'phil_sitemap.allow_localhost')]
        private bool $allowLocalhost,
    ) {
    }

    #[Override]
    public function provide(UrlContainer $urls, ?string $section): void
    {
        if (null !== $section && self::SECTION !== $section) {
            return;
        }

        foreach (self::PAGES as $route => $priority) {
            $hrefs = [];
            foreach ($this->locales as $locale) {
                $hrefs[$locale] = $this->urlGenerator->generate($route, ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            $alternates = [];
            foreach ($hrefs as $locale => $href) {
                $alternates[] = new Alternate($locale, $href, $this->allowLocalhost);
            }

            $alternates[] = new Alternate('x-default', $hrefs[$this->locales[0]], $this->allowLocalhost);

            foreach ($hrefs as $href) {
                $urls->add(new Url($href, priority: $priority, alternates: $alternates, allowLocalhost: $this->allowLocalhost), self::SECTION);
            }
        }
    }
}
