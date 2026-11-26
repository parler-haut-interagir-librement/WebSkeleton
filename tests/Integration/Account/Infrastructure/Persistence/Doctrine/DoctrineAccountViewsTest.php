<?php

declare(strict_types=1);

namespace App\Tests\Integration\Account\Infrastructure\Persistence\Doctrine;

use App\Account\Application\AccountView;
use App\Account\Application\AccountViews;
use App\Account\Domain\AccountId;
use App\Account\Infrastructure\Persistence\Doctrine\DoctrineAccountViews;
use App\Fixtures\UserFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(DoctrineAccountViews::class)]
final class DoctrineAccountViewsTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAnAccountIsFoundByItsIdentifier(): void
    {
        $user = UserFactory::createOne(['username' => 'ada', 'roles' => ['ROLE_ADMIN']]);
        UserFactory::createOne(['username' => 'grace']);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $entityManager->clear();

        $view = $this->views()->find(AccountId::fromString($user->getId()->toString()));

        self::assertInstanceOf(AccountView::class, $view);
        self::assertTrue($view->id->equals($user->getId()));
        self::assertSame('ada', $view->username);
        self::assertSame(['ROLE_USER', 'ROLE_ADMIN'], $view->roles);
        self::assertInstanceOf(DateTimeImmutable::class, $view->createdAt);
    }

    public function testAnUnknownIdentifierHasNoView(): void
    {
        UserFactory::createOne();

        self::assertNull($this->views()->find(AccountId::generate()));
    }

    public function testTheSerializerWritesTheIdentifierAsItsStringAndBuildsItBack(): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        self::assertInstanceOf(SerializerInterface::class, $serializer);
        $id = AccountId::generate();
        $view = new AccountView($id, 'ada', ['ROLE_USER'], new DateTimeImmutable('2026-11-26T10:00:00+00:00'));

        $json = $serializer->serialize($view, 'json');
        self::assertJsonStringEqualsJsonString(
            \sprintf('{"id":"%s","username":"ada","roles":["ROLE_USER"],"createdAt":"2026-11-26T10:00:00+00:00"}', $id->toString()),
            $json,
        );

        $copy = $serializer->deserialize($json, AccountView::class, 'json');
        self::assertInstanceOf(AccountView::class, $copy);
        self::assertInstanceOf(AccountId::class, $copy->id);
        self::assertTrue($copy->id->equals($id));
    }

    private function views(): DoctrineAccountViews
    {
        $views = self::getContainer()->get(AccountViews::class);
        self::assertInstanceOf(DoctrineAccountViews::class, $views);

        return $views;
    }
}
