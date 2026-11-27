<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The endpoints of the uptime monitors of config/routes/phil_monitoring.yaml, outside any firewall: liveness, readiness
 * with the checks of config/packages/phil_monitoring.yaml and the check of phil/error-tracking-bundle, and the detailed
 * view for the bearer of HEALTH_TOKEN only.
 */
#[CoversNothing]
final class HealthTest extends WebTestCase
{
    use ResetDatabase;

    private const string TOKEN = 'health-token-of-the-tests';

    public function testTheLivenessProbeAnswersWithoutASession(): void
    {
        $client = self::createClient();
        $client->request('GET', '/_health/live');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('{"status":"up"}', (string) $client->getResponse()->getContent());
        self::assertFalse($client->getResponse()->headers->has('Set-Cookie'));
        self::assertStringContainsString('no-store', (string) $client->getResponse()->headers->get('Cache-Control'));
    }

    public function testTheReadinessProbeRunsTheChecks(): void
    {
        $client = self::createClient();
        $client->request('GET', '/_health/ready');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('{"status":"up"}', (string) $client->getResponse()->getContent());
    }

    public function testTheDetailedViewIsRefusedWithoutTheToken(): void
    {
        $client = self::createClient();
        $client->request('GET', '/_health/details');

        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('WWW-Authenticate', 'Bearer');
    }

    public function testTheDetailedViewNamesEveryCheck(): void
    {
        $client = self::createClient();
        $client->request('GET', '/_health/details', server: ['HTTP_AUTHORIZATION' => 'Bearer '.self::TOKEN]);

        self::assertResponseIsSuccessful();

        /** @var array{status: string, checks: list<array{name: string, status: string}>} $details */
        $details = json_decode((string) $client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        self::assertSame('up', $details['status']);

        $statuses = array_column($details['checks'], 'status', 'name');

        self::assertSame('up', $statuses['error_tracking'] ?? null);
        self::assertSame('up', $statuses['database.default'] ?? null);
        self::assertSame('up', $statuses['cache.app'] ?? null);
    }
}
