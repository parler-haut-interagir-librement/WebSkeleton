<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\FeatureFlags;

use Override;
use Phil\FeatureFlagsBundle\Application\Admin\ActorProvider;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Who uses the screens of the feature flags: the user name of the account signed in, written in the audit line of
 * each change; nobody when no one is signed in, and every screen then answers 403.
 *
 * @see AdministratorActorProviderTest
 */
final readonly class AdministratorActorProvider implements ActorProvider
{
    public function __construct(
        private Security $security,
    ) {
    }

    #[Override]
    public function current(): ?string
    {
        return $this->security->getUser()?->getUserIdentifier();
    }
}
