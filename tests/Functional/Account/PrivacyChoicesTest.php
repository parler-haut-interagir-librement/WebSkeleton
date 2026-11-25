<?php

declare(strict_types=1);

namespace App\Tests\Functional\Account;

use App\Fixtures\UserFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The pages of phil/privacy-bundle, as the skeleton configures them: under the language prefix, in the layout of the
 * site, the privacy choices for members only.
 */
#[CoversNothing]
final class PrivacyChoicesTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAVisitorIsAskedToSignIn(): void
    {
        $client = self::createClient();
        $client->request('GET', '/en/privacy-choices');

        self::assertResponseRedirects('/en/login');
    }

    #[TestWith(['fr'])]
    #[TestWith(['en'])]
    public function testAMemberSeesTheExportAndTheDeletionInTheLayout(string $locale): void
    {
        $client = self::createClient();
        $client->loginUser(UserFactory::createOne());

        $crawler = $client->request('GET', '/'.$locale.'/privacy-choices');

        self::assertResponseIsSuccessful();
        self::assertSame($locale, $crawler->filter('html')->attr('lang'));
        self::assertSelectorExists('link[rel="stylesheet"][href*="phil-ux"]');
        self::assertSelectorExists('main form[action="/'.$locale.'/privacy-choices/export"]');
        self::assertSelectorExists('main a[href="/'.$locale.'/privacy-choices/erasure"]');
    }

    public function testTheConsentPageIsOpenToEveryone(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr/privacy-choices/consent');

        self::assertResponseIsSuccessful();
    }
}
