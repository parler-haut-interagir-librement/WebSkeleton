<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Infrastructure\Http\Controller\HomeAction;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as E2ETestCase;

// use Symfony\Component\Panther\PantherTestCase as E2ETestCase;

/**
 * Panther is not installed by default. To install it run "composer req --dev symfony/panther".
 * Above, comment the first use and uncomment the second one. Same in the test below, uncomment the first line and
 * comment the second one.
 *
 * @see https://github.com/symfony/panther
 */
#[CoversClass(HomeAction::class)]
final class HomePageTest extends E2ETestCase
{
    public function testTheHomePageOpensInFrench(): void
    {
        // $client = self::createPantherClient();
        $client = self::createClient();
        $client->followRedirects();
        $client->request('GET', '/', server: ['HTTP_ACCEPT_LANGUAGE' => 'fr-CA,fr;q=0.9']);

        self::assertSelectorTextContains('body', 'WebSkeleton');
        self::assertSelectorTextSame('h1', 'Bienvenue');
    }
}
