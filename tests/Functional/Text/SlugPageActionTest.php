<?php

declare(strict_types=1);

namespace App\Tests\Functional\Text;

use App\Fixtures\UserFactory;
use App\Text\Infrastructure\Http\Controller\SlugPageAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The feature flag of the template, text.slug-page, both ways: off, as declared, the page does not exist and the home
 * page does not link to it; turned on by an administrator under /admin/flags, both appear.
 */
#[CoversClass(SlugPageAction::class)]
final class SlugPageActionTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    #[TestWith(['fr'])]
    #[TestWith(['en'])]
    public function testWhileTheFlagIsOffThePageIsNotFoundAndNotLinked(string $locale): void
    {
        $client = self::createClient();

        $client->request('GET', '/'.$locale.'/slug');
        self::assertResponseStatusCodeSame(404);

        $client->request('GET', '/'.$locale);
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('a[href="/'.$locale.'/slug"]');
    }

    #[TestWith(['fr', 'La même chose, sur une page sans JavaScript', 'Le slug d’un titre'])]
    #[TestWith(['en', 'The same, on a page without JavaScript', 'The slug of a title'])]
    public function testOnceTurnedOnThePageAnswersAndIsLinked(string $locale, string $link, string $title): void
    {
        $client = self::createClient();
        $this->turnOn($client);

        $client->request('GET', '/'.$locale);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('a[href="/'.$locale.'/slug"]', $link);

        $client->request('GET', '/'.$locale.'/slug');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', $title);
        self::assertSelectorNotExists('output');
    }

    public function testTheSlugOfATitleIsShown(): void
    {
        $client = self::createClient();
        $this->turnOn($client);

        $client->request('GET', '/en/slug', ['title' => 'Hello Wonderful World']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('output', 'hello-wonderful-world');
        self::assertInputValueSame('title', 'Hello Wonderful World');
        self::assertSelectorNotExists('[role="alert"]');
    }

    public function testATitleWithoutSlugIsRefusedWithTheReason(): void
    {
        $client = self::createClient();
        $this->turnOn($client);

        $client->request('GET', '/en/slug', ['title' => '   ']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('#slug-error', 'A title is required.');
        self::assertSelectorExists('input[aria-invalid="true"][aria-describedby="slug-error"]');
        self::assertSelectorNotExists('output');
    }

    /**
     * Turns the flag on as an administrator would, from its screen, then signs them out.
     */
    private function turnOn(KernelBrowser $client): void
    {
        $client->loginUser(UserFactory::createOne(['roles' => ['ROLE_ADMIN']]));
        $client->request('POST', '/admin/flags/'.SlugPageAction::FLAG.'/toggle', [
            'active' => '1',
            'reason' => 'Shown to the visitors.',
        ], server: ['HTTP_SEC_FETCH_SITE' => 'same-origin']);
        self::assertResponseStatusCodeSame(303);
        $client->getCookieJar()->clear();
    }
}
