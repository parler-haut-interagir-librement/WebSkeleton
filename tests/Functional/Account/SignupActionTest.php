<?php

declare(strict_types=1);

namespace App\Tests\Functional\Account;

use App\Account\Infrastructure\Form\SignupForm;
use App\Account\Infrastructure\Http\Controller\SignupAction;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use App\Fixtures\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(SignupAction::class)]
#[CoversClass(SignupForm::class)]
final class SignupActionTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    #[TestWith(['fr', 'S’inscrire', 'Au moins 12 caractères.'])]
    #[TestWith(['en', 'Sign up', 'At least 12 characters.'])]
    public function testTheSignupPageShowsTheFormInItsLanguage(string $locale, string $title, string $hint): void
    {
        $client = self::createClient();
        $client->request('GET', '/'.$locale.'/signup');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', $title);
        self::assertSelectorExists('input[name="signup[username]"][autocomplete="username"]');
        self::assertSelectorExists('input[name="signup[email]"][type="email"]');
        self::assertSelectorExists('input[name="signup[plainPassword][first]"][minlength="12"]');
        self::assertSelectorExists('input[name="signup[plainPassword][second]"]');
        self::assertSelectorTextContains('main form', $hint);
    }

    public function testANewMemberIsSignedUpAndSignedIn(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/en/signup');
        $client->submit($crawler->filter('main form')->form(), [
            'signup[username]' => 'ada',
            'signup[email]' => ' Ada@Example.com ',
            'signup[plainPassword][first]' => 'a long password',
            'signup[plainPassword][second]' => 'a long password',
        ]);

        self::assertResponseRedirects('/en/account');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'ada@example.com');

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        self::assertSame(1, $entityManager->getRepository(User::class)->count(['email' => 'ada@example.com']));
    }

    public function testAnInvalidFormIsShownAgainWithItsErrors(): void
    {
        $client = self::createClient();
        UserFactory::createOne(['username' => 'ada', 'email' => 'ada@example.com']);
        $crawler = $client->request('GET', '/en/signup');
        $client->submit($crawler->filter('main form')->form(), [
            'signup[username]' => 'ada',
            'signup[email]' => 'ADA@example.com',
            'signup[plainPassword][first]' => 'short',
            'signup[plainPassword][second]' => 'other',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main form', 'This user name is already taken.');
        self::assertSelectorTextContains('main form', 'An account already uses this email address.');
        self::assertSelectorTextContains('main form', 'The two passwords are not the same.');
    }

    public function testASignedInMemberIsSentToItsAccount(): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne());
        $client->request('GET', '/en/signup');

        self::assertResponseRedirects('/en/account');
    }
}
