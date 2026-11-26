<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\FeatureFlags;

use App\Account\Domain\Role;
use Override;
use Phil\FeatureFlagsBundle\Application\Toggle\ToggleAuthorizer;
use Phil\FeatureFlagsBundle\Domain\Edit\EditRequest;
use Phil\FeatureFlagsBundle\Domain\Exception\ToggleRefused;
use Phil\FeatureFlagsBundle\Domain\Toggle\ToggleRequest;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Who may change a feature flag: an administrator, and nobody else. The access rule of /admin already keeps the
 * screens for them; this check holds for any other caller of the toggle, a command or a service.
 *
 * @see AdministratorToggleAuthorizerTest
 */
final readonly class AdministratorToggleAuthorizer implements ToggleAuthorizer
{
    public function __construct(
        private Security $security,
    ) {
    }

    #[Override]
    public function authorize(ToggleRequest $request): void
    {
        if (!$this->security->isGranted(Role::ADMIN)) {
            throw ToggleRefused::forFlag($request->flag, 'only an administrator changes a feature flag.');
        }
    }

    #[Override]
    public function authorizeEdit(EditRequest $request): void
    {
        if (!$this->security->isGranted(Role::ADMIN)) {
            throw ToggleRefused::forFlag($request->flag(), 'only an administrator changes a feature flag.');
        }
    }
}
