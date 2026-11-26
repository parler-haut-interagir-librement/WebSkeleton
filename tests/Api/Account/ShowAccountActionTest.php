<?php

declare(strict_types=1);

namespace App\Tests\Api\Account;

use App\Account\Infrastructure\Http\Api\ShowAccountAction;
use App\Fixtures\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ShowAccountAction::class)]
final class ShowAccountActionTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAnAdministratorReadsAnAccountAsJson(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['username' => 'boss', 'roles' => ['ROLE_ADMIN']]));

        $member = UserFactory::createOne(['username' => 'ada', 'email' => 'ada@example.com']);

        $client->request('GET', '/api/accounts/'.$member->getId()->toString());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        $content = (string) $client->getResponse()->getContent();
        /** @var array<string, mixed> $json */
        $json = json_decode($content, true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame($member->getId()->toString(), $json['id']);
        self::assertSame('ada', $json['username']);
        self::assertSame(['ROLE_USER'], $json['roles']);
        self::assertArrayHasKey('createdAt', $json);
        self::assertStringNotContainsString('ada@example.com', $content);
        self::assertStringNotContainsString('password', $content);
    }

    public function testAnythingButAKnownAccountIdAnswers404(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));

        foreach (['not-a-uuid', '0196b6a0-0000-4000-8000-000000000000', '0196b6a0-0000-7000-8000-000000000000'] as $id) {
            $client->request('GET', '/api/accounts/'.$id);
            self::assertResponseStatusCodeSame(404, $id);
        }
    }

    public function testAMemberIsRefused(): void
    {
        $client = self::createClient();
        $member = UserFactory::createOne();
        $client->loginUser($member);
        $client->request('GET', '/api/accounts/'.$member->getId()->toString());

        self::assertResponseStatusCodeSame(403);
    }

    public function testAVisitorIsAskedToSignIn(): void
    {
        $client = self::createClient();
        $member = UserFactory::createOne();
        $client->request('GET', '/api/accounts/'.$member->getId()->toString());

        self::assertResponseRedirects('/fr/login');
    }
}
