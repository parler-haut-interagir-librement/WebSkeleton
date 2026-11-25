<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Http\Api\SlugifyAction;
use App\Infrastructure\Http\Controller\HomeAction;
use App\Infrastructure\Http\Root\LocaleRedirectAction;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * Every GET route without a parameter, or whose only parameter is the language, answers without a server error, in
 * every language. Extracted and simplified from the "pierstoval/smoke-testing" package.
 *
 * @see https://github.com/Pierstoval/SmokeTesting
 */
#[CoversClass(HomeAction::class)]
#[CoversClass(LocaleRedirectAction::class)]
#[CoversClass(SlugifyAction::class)]
final class StaticRoutesSmokeTest extends WebTestCase
{
    #[DataProvider('provideRouteCollection')]
    public function testRoutesDoNotReturnInternalError(string $httpMethod, string $routeName, string $routePath): void
    {
        $client = self::createClient();
        $client->request($httpMethod, $routePath);

        self::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            \sprintf('Request "%s %s" for route "%s" returned an internal error.', $httpMethod, $routePath, $routeName),
        );
    }

    /**
     * @return Generator<string, array{httpMethod: string, routeName: string, routePath: string}>
     */
    public static function provideRouteCollection(): Generator
    {
        self::bootKernel();
        $router = self::getContainer()->get(RouterInterface::class);
        /** @var list<string> $locales */
        $locales = self::getContainer()->getParameter('app.locales');

        if (0 === $router->getRouteCollection()->count()) {
            throw new RuntimeException('No routes found in the application.');
        }

        $requests = [];
        foreach ($router->getRouteCollection() as $routeName => $route) {
            $methods = [] === $route->getMethods() ? ['GET'] : array_intersect($route->getMethods(), ['GET']);
            if ([] === $methods) {
                continue;
            }

            /** @var list<string> $variables */
            $variables = $route->compile()->getVariables();
            $missing = array_values(array_diff($variables, array_keys($route->getDefaults())));
            if ([] === $missing) {
                $paths = [$router->generate($routeName)];
            } elseif (['_locale'] === $missing) {
                $paths = array_map(static fn (string $locale): string => $router->generate($routeName, ['_locale' => $locale]), $locales);
            } else {
                continue;
            }

            foreach ($paths as $routePath) {
                $requests["GET {$routePath}"] = ['httpMethod' => 'GET', 'routeName' => $routeName, 'routePath' => $routePath];
            }
        }

        self::ensureKernelShutdown();

        yield from $requests;
    }
}
