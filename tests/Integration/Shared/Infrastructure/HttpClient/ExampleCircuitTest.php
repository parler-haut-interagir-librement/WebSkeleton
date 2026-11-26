<?php

declare(strict_types=1);

namespace App\Tests\Integration\Shared\Infrastructure\HttpClient;

use App\Tests\Support\MockResponses;
use App\Tests\Support\TestServices;
use Phil\CircuitBreakerBundle\Infrastructure\HttpClient\Exception\CircuitOpen;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The circuit of the template, http.example, around the scoped client example.client: five server errors within a
 * minute open it, and the next request is refused before it leaves.
 */
#[CoversNothing]
final class ExampleCircuitTest extends KernelTestCase
{
    public function testServerErrorsOpenTheCircuitAndTheNextRequestNeverLeaves(): void
    {
        $responses = TestServices::get(self::getContainer(), MockResponses::class, MockResponses::class);
        $responses->answerWith(503);

        $client = $this->client();

        for ($call = 0; $call < 5; ++$call) {
            self::assertSame(503, $client->request('GET', '/status')->getStatusCode());
        }

        self::assertCount(5, $responses->requested());
        self::assertSame('GET https://example.com/status', $responses->requested()[0]);

        try {
            $client->request('GET', '/status')->getStatusCode();
            self::fail('The circuit should be open.');
        } catch (CircuitOpen) {
            self::assertCount(5, $responses->requested());
        }
    }

    public function testSuccessfulAnswersKeepTheCircuitClosed(): void
    {
        $responses = TestServices::get(self::getContainer(), MockResponses::class, MockResponses::class);
        $client = $this->client();

        for ($call = 0; $call < 6; ++$call) {
            self::assertSame(200, $client->request('GET', '/status')->getStatusCode());
        }

        self::assertCount(6, $responses->requested());
    }

    private function client(): HttpClientInterface
    {
        return TestServices::get(self::getContainer(), 'test.example.client', HttpClientInterface::class);
    }
}
