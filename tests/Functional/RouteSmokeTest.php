<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Shared\Infrastructure\Http\Controller\HomeAction;
use App\Shared\Infrastructure\Http\Root\LocaleRedirectAction;
use App\Text\Infrastructure\Http\Api\SlugifyAction;
use Phil\DevTools\Testing\RouteSmokeTesting;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Every GET route without a parameter, or whose only parameter is the language, answers without a server error, in
 * every language of the site.
 */
#[CoversClass(HomeAction::class)]
#[CoversClass(LocaleRedirectAction::class)]
#[CoversClass(SlugifyAction::class)]
final class RouteSmokeTest extends WebTestCase
{
    use RouteSmokeTesting;

    /**
     * @return list<array<string, string>>
     */
    protected static function smokeRouteParameters(): array
    {
        return SiteRoutes::parameterSets(self::getContainer());
    }

    /**
     * @return list<string>
     */
    protected static function smokeRouteExclusions(): array
    {
        return SiteRoutes::EXCLUDED;
    }
}
