<?php

declare(strict_types=1);

namespace App\Tests\Integration\Account\Infrastructure\FeatureFlags;

use App\Account\Infrastructure\FeatureFlags\AdministratorActorProvider;
use App\Account\Infrastructure\FeatureFlags\AdministratorToggleAuthorizer;
use App\Account\Infrastructure\FeatureFlags\MemberContextResolver;
use Phil\FeatureFlagsBundle\Domain\Edit\EditRequest;
use Phil\FeatureFlagsBundle\Domain\Exception\ToggleRefused;
use Phil\FeatureFlagsBundle\Domain\Flag\FlagDefinition;
use Phil\FeatureFlagsBundle\Domain\Flag\FlagName;
use Phil\FeatureFlagsBundle\Domain\Toggle\ToggleReason;
use Phil\FeatureFlagsBundle\Domain\Toggle\ToggleRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\InMemoryUser;

/**
 * The adapters of phil/feature-flags-bundle that read the account signed in: the context of the targeting rules, the
 * operator of the screens and the check that only an administrator changes a flag.
 */
#[CoversClass(MemberContextResolver::class)]
#[CoversClass(AdministratorActorProvider::class)]
#[CoversClass(AdministratorToggleAuthorizer::class)]
final class FeatureFlagAdaptersTest extends KernelTestCase
{
    public function testAVisitorHasAnEmptyContextAndIsNoOperator(): void
    {
        self::assertNull($this->resolver()->current()->subject);
        self::assertNull($this->resolver()->current()->tenant);
        self::assertNull($this->resolver()->current()->project);
        self::assertNull($this->actors()->current());
    }

    public function testTheMemberSignedInIsTheSubjectAndTheOperator(): void
    {
        $this->signIn('ada', ['ROLE_USER']);

        self::assertSame('ada', $this->resolver()->current()->subject);
        self::assertNull($this->resolver()->current()->tenant);
        self::assertSame('ada', $this->actors()->current());
    }

    public function testAnAdministratorChangesAFlag(): void
    {
        $this->signIn('boss', ['ROLE_ADMIN']);

        $this->authorizer()->authorize($this->toggle());
        $this->authorizer()->authorizeEdit($this->edit());

        $this->expectNotToPerformAssertions();
    }

    public function testAMemberIsRefusedAToggle(): void
    {
        $this->signIn('ada', ['ROLE_USER']);

        $this->expectException(ToggleRefused::class);
        $this->expectExceptionMessageIsOrContains('only an administrator changes a feature flag.');

        $this->authorizer()->authorize($this->toggle());
    }

    public function testAMemberIsRefusedAnEdit(): void
    {
        $this->signIn('ada', ['ROLE_USER']);

        $this->expectException(ToggleRefused::class);
        $this->expectExceptionMessageIsOrContains('only an administrator changes a feature flag.');

        $this->authorizer()->authorizeEdit($this->edit());
    }

    public function testAVisitorIsRefused(): void
    {
        $this->expectException(ToggleRefused::class);

        $this->authorizer()->authorize($this->toggle());
    }

    /**
     * @param list<string> $roles
     */
    private function signIn(string $username, array $roles): void
    {
        $user = new InMemoryUser($username, null, $roles);
        self::getContainer()->get(TokenStorageInterface::class)->setToken(new UsernamePasswordToken($user, 'main', $roles));
    }

    private function toggle(): ToggleRequest
    {
        return new ToggleRequest(FlagName::of('text.slug-page'), true, 'boss', ToggleReason::of('A test.'));
    }

    private function edit(): EditRequest
    {
        return new EditRequest(new FlagDefinition(FlagName::of('text.new-page'), false), 'boss', ToggleReason::of('A test.'));
    }

    private function resolver(): MemberContextResolver
    {
        return self::getContainer()->get(MemberContextResolver::class);
    }

    private function actors(): AdministratorActorProvider
    {
        return self::getContainer()->get(AdministratorActorProvider::class);
    }

    private function authorizer(): AdministratorToggleAuthorizer
    {
        return self::getContainer()->get(AdministratorToggleAuthorizer::class);
    }
}
