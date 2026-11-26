<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\FeatureFlags;

use Override;
use Phil\FeatureFlagsBundle\Application\Toggle\ToggleAuditor;
use Phil\FeatureFlagsBundle\Domain\Edit\EditRecord;
use Phil\FeatureFlagsBundle\Domain\Flag\FlagDefinition;
use Phil\FeatureFlagsBundle\Domain\Toggle\ToggleRecord;
use Phil\FeatureFlagsBundle\Infrastructure\Configuration\FlagDefinitionFormat;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

/**
 * The audit trail of the feature flags: each change, before it is written, as one line of the "feature_flags" log
 * channel, with the administrator, the flag, the state or definition before and after, the reason and the time. A site
 * that keeps its trail in a table writes its own auditor; this one throws only when the logger does.
 *
 * @see LogToggleAuditorTest
 */
final readonly class LogToggleAuditor implements ToggleAuditor
{
    public function __construct(
        #[Target('feature_flags')]
        private LoggerInterface $logger,
    ) {
    }

    #[Override]
    public function record(ToggleRecord $record): void
    {
        $this->logger->notice('Feature flag {flag} turned {state} by {actor}: {reason}', [
            'flag' => $record->flag->value,
            'state' => $record->after ? 'on' : 'off',
            'actor' => $record->actor,
            'before' => $record->before,
            'after' => $record->after,
            'reason' => $record->reason->value,
            'at' => $record->at->format(\DATE_ATOM),
        ]);
    }

    #[Override]
    public function recordEdit(EditRecord $record): void
    {
        $this->logger->notice('Feature flag {flag} {change} by {actor}: {reason}', [
            'flag' => $record->flag->value,
            'change' => $record->before instanceof FlagDefinition ? 'edited' : 'created',
            'actor' => $record->actor,
            'before' => $record->before instanceof FlagDefinition ? FlagDefinitionFormat::write($record->before) : null,
            'after' => FlagDefinitionFormat::write($record->after),
            'reason' => $record->reason->value,
            'at' => $record->at->format(\DATE_ATOM),
        ]);
    }
}
