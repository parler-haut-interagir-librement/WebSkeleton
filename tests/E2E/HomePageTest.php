<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Shared\Infrastructure\Http\Controller\HomeAction;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Panther\PantherTestCase;

/**
 * The home page in a real browser, Chrome driven by Panther: the theme switcher stays hidden until its Stimulus
 * controller runs, so seeing it proves that the JavaScript of the page runs under the Content Security Policy. Run by
 * `castor test:e2e` alone; Chrome and chromedriver must be on the PATH.
 *
 * The host is localhost, not 127.0.0.1: phil/structured-data-bundle refuses an IP address as the URL of the site.
 *
 * @see https://github.com/symfony/panther
 */
#[CoversClass(HomeAction::class)]
final class HomePageTest extends PantherTestCase
{
    public function testTheJavaScriptOfTheHomePageRuns(): void
    {
        $client = self::createPantherClient(['hostname' => 'localhost']);
        $client->request('GET', '/fr');

        self::assertSelectorTextSame('h1', 'Bienvenue');

        $client->waitForVisibility('fieldset.isThemeSwitcher', 10);
        self::assertSelectorIsVisible('fieldset.isThemeSwitcher');
    }
}
