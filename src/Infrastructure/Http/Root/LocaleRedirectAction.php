<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Root;

use App\Infrastructure\Http\Controller\HomeAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * "/" sends the visitor to the home page in the language their browser prefers among those of the site, the
 * default one when it prefers none of them. The answer varies with Accept-Language and is never cached as one page.
 *
 * @see LocaleRedirectActionTest
 */
#[AsController]
final readonly class LocaleRedirectAction
{
    /**
     * @param list<string> $locales the languages of the site, the default one first
     */
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private array $locales,
    ) {
    }

    #[Route(path: '/', name: self::class, methods: ['GET', 'HEAD'])]
    public function __invoke(Request $request): RedirectResponse
    {
        $locale = $request->getPreferredLanguage($this->locales) ?? $this->locales[0];

        $response = new RedirectResponse($this->urlGenerator->generate(HomeAction::class, ['_locale' => $locale]));
        $response->setVary('Accept-Language');

        return $response;
    }
}
