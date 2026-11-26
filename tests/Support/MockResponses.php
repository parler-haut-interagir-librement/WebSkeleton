<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * The answers of every HTTP client in the test environment, set as framework.http_client.mock_response_factory: no
 * request of the tests leaves the machine. A test sets the status the third party answers with, 200 by default, and
 * reads the URLs it was asked.
 */
final class MockResponses
{
    private int $status = 200;

    /**
     * @var list<string>
     */
    private array $requested = [];

    public function answerWith(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return list<string>
     */
    public function requested(): array
    {
        return $this->requested;
    }

    public function __invoke(string $method, string $url): ResponseInterface
    {
        $this->requested[] = $method.' '.$url;

        return new MockResponse('', ['http_code' => $this->status]);
    }
}
