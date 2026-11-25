<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Shared\Infrastructure\Http\Controller\HomeAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(HomeAction::class)]
final class HomeActionTest extends WebTestCase
{
    #[TestWith(['fr', 'Bienvenue', 'Accueil'])]
    #[TestWith(['en', 'Welcome', 'Home'])]
    public function testTheHomePageIsInTheLanguageOfItsPath(string $locale, string $title, string $menu): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/'.$locale);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', $title);
        self::assertSame($locale, $crawler->filter('html')->attr('lang'));
        self::assertResponseHeaderSame('Content-Language', $locale);
        self::assertSelectorTextContains('header nav a[aria-current="page"]', $menu);
    }

    public function testTheLanguageSwitcherKeepsThePage(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/fr/contact');

        self::assertResponseIsSuccessful();
        self::assertSame('/en/contact', $crawler->filter('a[hreflang="en"]')->attr('href'));
        self::assertSame('true', $crawler->filter('a[hreflang="fr"]')->attr('aria-current'));
        self::assertCount(2, $crawler->filter('link[rel="alternate"][hreflang]'));
    }

    public function testAnUnknownLanguageIsNotFound(): void
    {
        $client = self::createClient();
        $client->request('GET', '/de');

        self::assertResponseStatusCodeSame(404);
    }
}
