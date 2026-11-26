<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\FeatureFlags;

use Override;
use Phil\FeatureFlagsBundle\Application\Flag\EvaluationContextResolver;
use Phil\FeatureFlagsBundle\Domain\Flag\EvaluationContext;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * The context the feature flags are evaluated in: the member signed in is the subject, so that a flag can be turned on
 * for some members or a share of them; a visitor has an empty context, and a targeted flag is off for them. The site
 * has no tenant and no project.
 *
 * @see MemberContextResolverTest
 */
final readonly class MemberContextResolver implements EvaluationContextResolver
{
    public function __construct(
        private Security $security,
    ) {
    }

    #[Override]
    public function current(): EvaluationContext
    {
        return EvaluationContext::of(subject: $this->security->getUser()?->getUserIdentifier());
    }
}
