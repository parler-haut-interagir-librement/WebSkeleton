<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Fixtures\UserFactory;
use App\Shared\Infrastructure\Http\Controller\HomeAction;
use Phil\AccessibilityCheck\AccessibilityChecker;
use Phil\AccessibilityCheck\Finding;
use Phil\AccessibilityCheck\Policy;
use Phil\AccessibilityCheck\Report;
use Phil\AccessibilityCheck\Severity;
use Phil\DevTools\Testing\SmokeRoutes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Every page the route smoke test requests is checked by phil/accessibility-check, in every language of the site, for
 * a visitor and for a signed-in administrator: an error fails the test, a warning is written out and passes. A
 * response that is not a successful HTML page — a redirect, a 404, XML, JSON — has nothing to check.
 *
 * A route is left out by adding the prefix of its name to EXCLUDED_ROUTES, a rule by adding its identifier to
 * DISABLED_RULES, each with the reason in a comment.
 */
#[CoversClass(HomeAction::class)]
final class AccessibilityTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    /**
     * The prefixes of the route names left out of this test, besides those of SiteRoutes::EXCLUDED.
     *
     * @var list<string>
     */
    private const array EXCLUDED_ROUTES = [
        // The pages of EasyAdmin, /admin and below: its layout names the user menu and the settings menu by an icon
        // alone (link-name, button-name). The site's own pages are checked.
        'admin',
    ];

    /**
     * The identifiers of the rules of phil/accessibility-check turned off for every page.
     *
     * @var list<string>
     */
    private const array DISABLED_RULES = [];

    #[DataProvider('providePages')]
    public function testAVisitorMeetsNoAccessibilityError(string $routeName, string $path): void
    {
        $this->assertAccessible(self::createClient(), $routeName, $path);
    }

    #[DataProvider('providePages')]
    public function testAnAdministratorMeetsNoAccessibilityError(string $routeName, string $path): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));

        $this->assertAccessible($client, $routeName, $path);
    }

    /**
     * @return array<string, array{routeName: string, path: string}>
     */
    public static function providePages(): array
    {
        try {
            $router = self::getContainer()->get(RouterInterface::class);
            $requests = SmokeRoutes::from(
                $router->getRouteCollection(),
                $router,
                SiteRoutes::parameterSets(self::getContainer()),
                [...SiteRoutes::EXCLUDED, ...self::EXCLUDED_ROUTES],
            );
        } finally {
            self::ensureKernelShutdown();
        }

        if ([] === $requests) {
            throw new RuntimeException('The application has no page to check.');
        }

        return $requests;
    }

    private function assertAccessible(KernelBrowser $client, string $routeName, string $path): void
    {
        $client->request('GET', $path);
        $response = $client->getResponse();

        $isPage = $response->isSuccessful() && str_starts_with((string) $response->headers->get('Content-Type'), 'text/html');

        if (!$isPage) {
            // A redirect, a 404, XML or JSON: no page to check.
            self::assertFalse($isPage);

            return;
        }

        $report = new AccessibilityChecker(new Policy()->withoutRules(...self::DISABLED_RULES))->checkHtml((string) $response->getContent());

        $warnings = $this->describe($report, $report->withSeverity(Severity::Warning));
        if ([] !== $warnings) {
            fwrite(\STDERR, \sprintf("\nAccessibility warnings on GET %s (%s):\n%s\n", $path, $routeName, implode("\n", $warnings)));
        }

        self::assertSame([], $this->describe($report, $report->withSeverity(Severity::Error)), \sprintf('GET %s, of the route "%s", has accessibility errors.', $path, $routeName));
    }

    /**
     * @param list<Finding> $findings
     *
     * @return list<string>
     */
    private function describe(Report $report, array $findings): array
    {
        return array_map(
            static fn (Finding $finding): string => \sprintf('[%s, WCAG %s] %s: %s', $finding->rule, $finding->criterion->value, $finding->location->describe(), $report->message($finding)),
            $findings,
        );
    }
}
