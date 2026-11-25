<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Infrastructure\Http\Api\SlugifyAction;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(SlugifyAction::class)]
final class SlugifyActionTest extends WebTestCase
{
    public function testItAnswersTheSlugOfATitle(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/slugify?title=This IS the _-! Web SKELETON  project');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertJsonStringEqualsJsonString('{"slug":"this-is-the-web-skeleton-project"}', (string) $client->getResponse()->getContent());
    }

    public function testATitleTheDomainRefusesAnswers422(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/slugify?title=%20');

        self::assertResponseStatusCodeSame(422);
        self::assertJsonStringEqualsJsonString('{"error":"A title is required."}', (string) $client->getResponse()->getContent());
    }

    public function testAMissingTitleAnswers422(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/slugify');

        self::assertResponseStatusCodeSame(422);
    }
}
