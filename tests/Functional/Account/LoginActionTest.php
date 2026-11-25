<?php

declare(strict_types=1);

namespace App\Tests\Functional\Account;

use App\Account\Infrastructure\Http\Controller\AccountAction;
use App\Account\Infrastructure\Http\Controller\LoginAction;
use App\Fixtures\AppStory;
use App\Fixtures\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(LoginAction::class)]
#[CoversClass(AccountAction::class)]
final class LoginActionTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    #[TestWith(['fr', 'Se connecter'])]
    #[TestWith(['en', 'Sign in'])]
    public function testTheLoginPageShowsTheFormInItsLanguage(string $locale, string $title): void
    {
        $client = self::createClient();
        $client->request('GET', '/'.$locale.'/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', $title);
        self::assertSelectorExists('form input[name="_username"]');
        self::assertSelectorExists('form input[name="_password"][type="password"]');
        self::assertSelectorExists('form input[name="_csrf_token"]');
        self::assertSelectorExists('header nav a[aria-current="page"][href="/'.$locale.'/login"]');
    }

    public function testAMemberSignsInAndReachesItsAccount(): void
    {
        $client = self::createClient();
        AppStory::load();
        $crawler = $client->request('GET', '/en/login');
        $client->submit($crawler->filter('main form')->form(), [
            '_username' => AppStory::MEMBER_USERNAME,
            '_password' => AppStory::MEMBER_PASSWORD,
        ]);

        self::assertResponseRedirects('/en/account');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'My account');
        self::assertSelectorTextContains('main', AppStory::MEMBER_USERNAME);
        self::assertSelectorTextContains('main', 'member@example.com');
        self::assertSelectorExists('main a[href="/en/privacy-choices"]');
    }

    public function testAWrongPasswordIsRefused(): void
    {
        $client = self::createClient();
        AppStory::load();
        $crawler = $client->request('GET', '/en/login');
        $client->submit($crawler->filter('main form')->form(), [
            '_username' => AppStory::MEMBER_USERNAME,
            '_password' => 'not-the-password',
        ]);

        self::assertResponseRedirects('/en/login');
        $client->followRedirect();
        self::assertSelectorTextContains('[role="alert"]', 'Invalid credentials.');
        self::assertInputValueSame('_username', AppStory::MEMBER_USERNAME);
    }

    public function testASignedInMemberIsSentToItsAccount(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne(['username' => 'signed-in']));
        $client->request('GET', '/fr/login');

        self::assertResponseRedirects('/fr/account');
    }

    public function testTheAccountPageAsksAVisitorToSignIn(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr/account');

        self::assertResponseRedirects('/fr/login');
    }
}
