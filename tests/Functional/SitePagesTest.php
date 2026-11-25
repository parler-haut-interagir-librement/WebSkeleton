<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The pages the PHIL bundles add to the site, as the skeleton configures them.
 */
#[CoversNothing]
final class SitePagesTest extends WebTestCase
{
    #[TestWith(['/fr/contact', 'Contact'])]
    #[TestWith(['/en/contact', 'Contact'])]
    public function testTheContactPageHasItsForm(string $path, string $title): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', $title);
        self::assertGreaterThan(0, $crawler->filter('main form textarea')->count());
    }

    public function testAnEmptyMessageIsRefusedWithoutAnError(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/en/contact');
        $form = $crawler->filter('main form')->form();
        $client->submit($form);

        self::assertLessThan(500, $client->getResponse()->getStatusCode());
        self::assertSelectorTextSame('h1', 'Contact');
    }

    #[TestWith(['/fr/legal'])]
    #[TestWith(['/en/privacy'])]
    #[TestWith(['/fr/accessibilite'])]
    #[TestWith(['/en/accessibility'])]
    public function testDraftDocumentsAreNotPublished(string $path): void
    {
        $client = self::createClient();
        $client->request('GET', $path);

        self::assertResponseStatusCodeSame(404);
    }

    public function testTheSitemapListsTheHomePageInEveryLanguage(): void
    {
        $client = self::createClient();
        $client->request('GET', '/sitemap.xml');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('<loc>http://localhost/sitemap.main.xml</loc>', (string) $client->getResponse()->getContent());

        $client->request('GET', '/sitemap.main.xml');
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('<loc>http://localhost/fr</loc>', $content);
        self::assertStringContainsString('<loc>http://localhost/en/contact</loc>', $content);
        self::assertStringContainsString('hreflang="x-default"', $content);
    }

    public function testSecurityTxtNamesTheContact(): void
    {
        $client = self::createClient();
        $client->request('GET', '/.well-known/security.txt');

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Contact: mailto:security@example.com', $content);
        self::assertStringContainsString('Preferred-Languages: fr, en', $content);
    }

    public function testAnErrorPageIsInTheLayout(): void
    {
        $client = self::createClient(['debug' => false]);
        $client->request('GET', '/fr/not-a-page');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextSame('h1', 'Erreur 404');
        self::assertSelectorExists('link[href*="phil-ux"]');
    }
}
