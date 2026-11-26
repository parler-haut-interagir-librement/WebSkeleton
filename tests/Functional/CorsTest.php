<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The cross-origin rules of config/packages/phil_cors.yaml: the JSON endpoints under /api, for the origins that
 * CORS_ALLOW_ORIGIN matches (localhost and 127.0.0.1 on any port in .env), GET only, without credentials.
 */
#[CoversNothing]
final class CorsTest extends WebTestCase
{
    private const string ALLOWED_ORIGIN = 'http://localhost:5173';

    public function testAPreflightFromAnAllowedOriginIsAnsweredWithThePolicy(): void
    {
        $client = self::createClient();
        $this->preflight($client, self::ALLOWED_ORIGIN, 'GET', 'Content-Type');

        self::assertResponseStatusCodeSame(204);
        self::assertResponseHeaderSame('Access-Control-Allow-Origin', self::ALLOWED_ORIGIN);
        self::assertResponseHeaderSame('Access-Control-Allow-Methods', 'GET');
        self::assertResponseHeaderSame('Access-Control-Allow-Headers', 'Content-Type');
        self::assertResponseHeaderSame('Access-Control-Max-Age', '3600');
        self::assertResponseNotHasHeader('Access-Control-Allow-Credentials');
        self::assertStringContainsString('Origin', (string) $client->getResponse()->headers->get('Vary'));
    }

    public function testAPreflightFrom127001IsAllowedToo(): void
    {
        $client = self::createClient();
        $this->preflight($client, 'http://127.0.0.1:8000', 'GET');

        self::assertResponseStatusCodeSame(204);
        self::assertResponseHeaderSame('Access-Control-Allow-Origin', 'http://127.0.0.1:8000');
    }

    public function testASimpleRequestFromAnAllowedOriginGetsItsOriginBack(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/slugify?title=Cross origin', server: ['HTTP_ORIGIN' => self::ALLOWED_ORIGIN]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Access-Control-Allow-Origin', self::ALLOWED_ORIGIN);
        self::assertResponseNotHasHeader('Access-Control-Allow-Credentials');
        self::assertStringContainsString('Origin', (string) $client->getResponse()->headers->get('Vary'));
        self::assertJsonStringEqualsJsonString('{"slug":"cross-origin"}', (string) $client->getResponse()->getContent());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function foreignOrigins(): iterable
    {
        yield 'another host' => ['https://evil.test'];
        yield 'an allowed host as a prefix' => ['http://localhost.evil.test'];
        yield 'an allowed host as a suffix' => ['http://evillocalhost'];
        yield 'the null origin' => ['null'];
    }

    #[DataProvider('foreignOrigins')]
    public function testAPreflightFromAForeignOriginIsRefused(string $origin): void
    {
        $client = self::createClient();
        $this->preflight($client, $origin, 'GET');

        self::assertResponseStatusCodeSame(204);
        self::assertResponseNotHasHeader('Access-Control-Allow-Origin');
        self::assertResponseNotHasHeader('Access-Control-Allow-Methods');
    }

    #[DataProvider('foreignOrigins')]
    public function testASimpleRequestFromAForeignOriginGetsNoAllowOrigin(string $origin): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/slugify?title=Cross origin', server: ['HTTP_ORIGIN' => $origin]);

        self::assertResponseIsSuccessful();
        self::assertResponseNotHasHeader('Access-Control-Allow-Origin');
    }

    public function testAMethodOutsideThePolicyIsRefused(): void
    {
        $client = self::createClient();
        $this->preflight($client, self::ALLOWED_ORIGIN, 'DELETE');

        self::assertResponseStatusCodeSame(204);
        self::assertResponseNotHasHeader('Access-Control-Allow-Origin');
    }

    public function testAHeaderOutsideThePolicyIsRefused(): void
    {
        $client = self::createClient();
        $this->preflight($client, self::ALLOWED_ORIGIN, 'GET', 'Authorization');

        self::assertResponseStatusCodeSame(204);
        self::assertResponseNotHasHeader('Access-Control-Allow-Origin');
    }

    public function testThePagesOutsideApiHaveNoCrossOriginHeader(): void
    {
        $client = self::createClient();
        $client->request('GET', '/en', server: ['HTTP_ORIGIN' => self::ALLOWED_ORIGIN]);

        self::assertResponseIsSuccessful();
        self::assertResponseNotHasHeader('Access-Control-Allow-Origin');
    }

    private function preflight(KernelBrowser $client, string $origin, string $method, ?string $headers = null): void
    {
        $server = ['HTTP_ORIGIN' => $origin, 'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => $method];
        if (null !== $headers) {
            $server['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] = $headers;
        }

        $client->request('OPTIONS', '/api/slugify', server: $server);
    }
}
