<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Http\Root\LocaleRedirectAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(LocaleRedirectAction::class)]
final class LocaleRedirectActionTest extends WebTestCase
{
    #[TestWith(['fr-CA,fr;q=0.9,en;q=0.8', '/fr'])]
    #[TestWith(['en-US,en;q=0.9', '/en'])]
    #[TestWith(['de-DE,de;q=0.9', '/fr'])]
    #[TestWith(['', '/fr'])]
    public function testTheRootSendsToThePreferredLanguage(string $acceptLanguage, string $expected): void
    {
        $client = self::createClient();
        $client->request('GET', '/', server: ['HTTP_ACCEPT_LANGUAGE' => $acceptLanguage]);

        self::assertResponseRedirects($expected, 302);
        self::assertStringContainsString('Accept-Language', (string) $client->getResponse()->headers->get('Vary'));
    }
}
