<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Fixtures\UserFactory;
use App\Shared\Infrastructure\Http\Admin\DashboardController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(DashboardController::class)]
final class DashboardControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAVisitorIsAskedToSignIn(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/fr/login');
    }

    public function testAMemberIsRefused(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne());
        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAnAdministratorReachesTheDashboardInThePhilUxTheme(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));

        $crawler = $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'WebSkeleton');
        self::assertCount(1, $crawler->filter('link[rel="stylesheet"][href*="phil-ux-easyadmin"]'));
        self::assertCount(1, $crawler->filter('a[href="http://localhost/admin/user"]'));
        self::assertCount(1, $crawler->filter('a[href="http://localhost/admin/errors"]'));
        self::assertCount(1, $crawler->filter('a[href="/fr"]'));
        self::assertGreaterThan(0, $crawler->filter('a[href="/fr/account"]')->count());
        self::assertCount(0, $crawler->filter('a[href*="logout"]'));
    }

    public function testTheAccountsAreListedAndShown(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['username' => 'boss', 'roles' => ['ROLE_ADMIN']]));

        $member = UserFactory::createOne(['username' => 'ada', 'email' => 'ada@example.com']);
        $client->request('GET', '/admin/user');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'ada@example.com');
        self::assertSelectorNotExists('a[href="/admin/user/new"]');

        $client->request('GET', '/admin/user/'.$member->getId()->toString());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'ada@example.com');

        $client->request('GET', '/admin/user/new');
        self::assertResponseStatusCodeSame(403);
    }

    public function testTheErrorsAreInTheDashboard(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));
        $client->request('GET', '/admin/errors');

        self::assertResponseIsSuccessful();
    }
}
