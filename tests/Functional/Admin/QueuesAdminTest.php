<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Fixtures\UserFactory;
use App\Shared\Infrastructure\Http\Admin\DashboardController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The page of the Messenger queues of phil/monitoring-bundle, /admin/queues: behind the access rule of /admin, linked
 * from the dashboard, in the layout of the site.
 */
#[CoversClass(DashboardController::class)]
final class QueuesAdminTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAVisitorIsAskedToSignIn(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/queues/');

        self::assertResponseRedirects('/fr/login');
    }

    public function testAMemberIsRefused(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne());
        $client->request('GET', '/admin/queues/');

        self::assertResponseStatusCodeSame(403);
    }

    public function testTheDashboardLinksToTheQueues(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));

        $crawler = $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href="/admin/queues/"]'));
    }

    public function testAnAdministratorSeesTheQueuesInTheLayoutOfTheSite(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));

        $crawler = $client->request('GET', '/admin/queues/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'sync');
        self::assertCount(1, $crawler->filter('main a[href="/admin"]'));
        self::assertCount(1, $crawler->filter('main [lang="en"]'));
        self::assertCount(1, $crawler->filter('link[rel="stylesheet"][href*="phil-ux"]'));
    }
}
