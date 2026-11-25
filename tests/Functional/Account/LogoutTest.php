<?php

declare(strict_types=1);

namespace App\Tests\Functional\Account;

use App\Fixtures\UserFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The sign-out of the firewall: a POST with the CSRF token "logout", never a GET.
 */
#[CoversNothing]
final class LogoutTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testTheButtonOfTheLayoutSignsTheMemberOut(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne());

        $crawler = $client->request('GET', '/en');
        $client->submit($crawler->filter('header form')->form());

        self::assertResponseRedirects('/');
        $client->request('GET', '/en/account');
        self::assertResponseRedirects('/en/login');
    }

    public function testAGetNeverSignsOut(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne());
        $client->request('GET', '/en/logout');

        self::assertResponseStatusCodeSame(405);
        $client->request('GET', '/en/account');
        self::assertResponseIsSuccessful();
    }

    public function testAForgedTokenNeverSignsOut(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne());
        $client->request('POST', '/en/logout', ['_csrf_token' => 'forged']);

        $client->request('GET', '/en/account');
        self::assertResponseIsSuccessful();
    }
}
