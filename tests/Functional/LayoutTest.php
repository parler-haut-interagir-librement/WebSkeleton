<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Shared\Infrastructure\Twig\EnvExtension;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The layout: phil-ux, the PHIL identity, the security headers and the JSON-LD of every page.
 */
#[CoversNothing]
final class LayoutTest extends WebTestCase
{
    public function testTheLayoutCarriesTheStylesheetAndTheIdentity(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/en');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('link[rel="stylesheet"][href*="phil-ux"]'));
        self::assertSelectorExists('a.isSkipLink[href="#content"]');
        self::assertSelectorExists('fieldset.isThemeSwitcher');
        self::assertSelectorTextContains('footer', 'Proclaim Honestly, Interact Liberally');
        self::assertSelectorTextContains('footer', 'COil/Strangebuzz');
        self::assertSelectorExists('footer a[href="https://ph-il.ca"]');
        self::assertSelectorExists('script[type="application/ld+json"]');
        self::assertStringNotContainsString('pico', (string) $client->getResponse()->getContent());
    }

    public function testEveryPageSendsTheSecurityHeaders(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/fr');

        $headers = $client->getResponse()->headers;
        $policy = (string) $headers->get('Content-Security-Policy');
        self::assertStringContainsString("default-src 'self'", $policy);
        self::assertStringContainsString("frame-ancestors 'none'", $policy);
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));

        // The inline scripts carry the nonce of the policy.
        foreach ($crawler->filter('script:not([src])') as $script) {
            $nonce = $script->attributes?->getNamedItem('nonce')?->nodeValue;
            self::assertNotNull($nonce);
            self::assertStringContainsString("'nonce-".$nonce."'", $policy);
        }
    }

    public function testTheFooterShowsTheVersions(): void
    {
        $client = self::createClient();
        $client->request('GET', '/en');

        $globals = new EnvExtension()->getGlobals();
        self::assertSelectorTextContains('footer', 'Symfony '.$globals['sf_version']);
    }
}
