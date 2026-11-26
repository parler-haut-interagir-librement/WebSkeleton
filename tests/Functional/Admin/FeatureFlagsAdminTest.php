<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Account\Infrastructure\FeatureFlags\AdministratorActorProvider;
use App\Account\Infrastructure\FeatureFlags\AdministratorToggleAuthorizer;
use App\Account\Infrastructure\FeatureFlags\LogToggleAuditor;
use App\Fixtures\UserFactory;
use App\Shared\Infrastructure\Http\Admin\DashboardController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The screens of phil/feature-flags-bundle, /admin/flags: behind the access rule of /admin, linked from the dashboard,
 * in the layout of the site, and each change posted from the site itself.
 */
#[CoversClass(DashboardController::class)]
#[CoversClass(AdministratorActorProvider::class)]
#[CoversClass(AdministratorToggleAuthorizer::class)]
#[CoversClass(LogToggleAuditor::class)]
final class FeatureFlagsAdminTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAVisitorIsAskedToSignIn(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/flags/');

        self::assertResponseRedirects('/fr/login');
    }

    public function testAMemberIsRefused(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne());
        $client->request('GET', '/admin/flags/');

        self::assertResponseStatusCodeSame(403);
    }

    public function testTheDashboardLinksToTheFlags(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));

        $crawler = $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href="/admin/flags/"]'));
    }

    public function testAnAdministratorSeesTheFlagsInTheLayoutOfTheSite(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));

        $crawler = $client->request('GET', '/admin/flags/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Feature flags');
        self::assertSelectorTextContains('main', 'text.slug-page');
        self::assertSelectorTextContains('main', 'circuit-open.http.example');
        self::assertCount(1, $crawler->filter('main a[href="/admin"]'));
        self::assertCount(1, $crawler->filter('main [lang="en"]'));
        self::assertCount(1, $crawler->filter('link[rel="stylesheet"][href*="phil-ux"]'));
    }

    public function testAnAdministratorTurnsAFlagOnWithAReason(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['username' => 'boss', 'roles' => ['ROLE_ADMIN']]));
        $client->request('POST', '/admin/flags/text.slug-page/toggle', [
            'active' => '1',
            'reason' => 'Shown to the visitors.',
        ], server: ['HTTP_SEC_FETCH_SITE' => 'same-origin']);

        self::assertResponseRedirects('/admin/flags/text.slug-page?done=toggled', 303);

        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'stored');
    }

    public function testAChangeWithoutReasonIsRefused(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));
        $client->request('POST', '/admin/flags/text.slug-page/toggle', [
            'active' => '1',
            'reason' => ' ',
        ], server: ['HTTP_SEC_FETCH_SITE' => 'same-origin']);

        self::assertResponseStatusCodeSame(422);
    }

    public function testAChangePostedFromAnotherSiteIsRefused(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));
        $client->request('POST', '/admin/flags/text.slug-page/toggle', [
            'active' => '1',
            'reason' => 'Shown to the visitors.',
        ], server: ['HTTP_SEC_FETCH_SITE' => 'cross-site']);

        self::assertResponseStatusCodeSame(403);
    }
}
