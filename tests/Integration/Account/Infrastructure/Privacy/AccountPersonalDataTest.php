<?php

declare(strict_types=1);

namespace App\Tests\Integration\Account\Infrastructure\Privacy;

use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use App\Account\Infrastructure\Privacy\AccountPersonalData;
use App\Fixtures\UserFactory;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Phil\PrivacyBundle\Application\SubjectErasure;
use Phil\PrivacyBundle\Application\SubjectExport;
use Phil\PrivacyBundle\Domain\Retention\ErasureEffect;
use Phil\PrivacyBundle\Domain\Subject\DataSubject;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(AccountPersonalData::class)]
final class AccountPersonalDataTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testTheModuleIsKnownToThePrivacyBundle(): void
    {
        $module = $this->module();

        self::assertSame('account', $module->module()->value);
        self::assertInstanceOf(SubjectExport::class, self::getContainer()->get(SubjectExport::class));
        self::assertInstanceOf(SubjectErasure::class, self::getContainer()->get(SubjectErasure::class));
    }

    public function testTheExportGivesWhatTheAccountHolds(): void
    {
        $user = UserFactory::createOne(['username' => 'ada', 'email' => 'ada@example.com', 'roles' => ['ROLE_ADMIN']]);
        UserFactory::createOne(['username' => 'grace']);

        $export = $this->module()->export(DataSubject::fromString($user->dataSubjectIdentifier()));

        self::assertSame([
            'account' => [
                'username' => 'ada',
                'email' => 'ada@example.com',
                'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
                'created_at' => $user->createdAt()?->format(DateTimeInterface::ATOM),
                'updated_at' => $user->updatedAt()?->format(DateTimeInterface::ATOM),
            ],
        ], $export);
    }

    public function testAnUnknownMemberHasNothingToExport(): void
    {
        UserFactory::createOne();

        self::assertSame([], $this->module()->export(DataSubject::fromString('0196b6a0-0000-7000-8000-000000000000')));
        self::assertSame([], $this->module()->export(DataSubject::fromString('not-a-uuid')));
        self::assertSame([], $this->module()->export(DataSubject::fromString('0196b6a0-0000-4000-8000-000000000000')));
    }

    public function testTheErasureDeletesTheAccountOnlyAndCanRunAgain(): void
    {
        $user = UserFactory::createOne(['username' => 'ada']);
        UserFactory::createOne(['username' => 'grace']);
        $subject = DataSubject::fromString($user->dataSubjectIdentifier());

        $report = $this->module()->erase($subject);
        $again = $this->module()->erase($subject);
        $this->module()->erase(DataSubject::fromString('not-a-uuid'));

        self::assertSame('account', $report->module->value);
        self::assertSame(['user' => ErasureEffect::Deleted], $report->effects);
        self::assertEquals($report, $again);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $entityManager->clear();
        $usernames = array_map(static fn (User $user): string => $user->getUsername(), $entityManager->getRepository(User::class)->findAll());
        self::assertSame(['grace'], $usernames);
    }

    private function module(): AccountPersonalData
    {
        $module = self::getContainer()->get(AccountPersonalData::class);
        self::assertInstanceOf(AccountPersonalData::class, $module);

        return $module;
    }
}
