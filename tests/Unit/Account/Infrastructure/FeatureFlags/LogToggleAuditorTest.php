<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Infrastructure\FeatureFlags;

use App\Account\Infrastructure\FeatureFlags\LogToggleAuditor;
use App\Tests\Support\RecordingLogger;
use DateTimeImmutable;
use Override;
use Phil\FeatureFlagsBundle\Domain\Edit\EditRecord;
use Phil\FeatureFlagsBundle\Domain\Flag\FlagDefinition;
use Phil\FeatureFlagsBundle\Domain\Flag\FlagName;
use Phil\FeatureFlagsBundle\Domain\Toggle\ToggleReason;
use Phil\FeatureFlagsBundle\Domain\Toggle\ToggleRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[CoversClass(LogToggleAuditor::class)]
final class LogToggleAuditorTest extends TestCase
{
    private RecordingLogger $logger;

    #[Override]
    protected function setUp(): void
    {
        $this->logger = new RecordingLogger();
    }

    public function testAToggleIsOneNoticeWithWhoWhatAndWhy(): void
    {
        $this->auditor()->record(new ToggleRecord(
            FlagName::of('text.slug-page'),
            'boss',
            false,
            true,
            ToggleReason::of('Shown to the visitors.'),
            new DateTimeImmutable('2026-11-26T10:00:00+00:00'),
        ));

        self::assertSame([[
            'level' => LogLevel::NOTICE,
            'message' => 'Feature flag {flag} turned {state} by {actor}: {reason}',
            'context' => [
                'flag' => 'text.slug-page',
                'state' => 'on',
                'actor' => 'boss',
                'before' => false,
                'after' => true,
                'reason' => 'Shown to the visitors.',
                'at' => '2026-11-26T10:00:00+00:00',
            ],
        ]], $this->logger->lines);
    }

    public function testATurnOffSaysOff(): void
    {
        $this->auditor()->record(new ToggleRecord(
            FlagName::of('text.slug-page'),
            'boss',
            true,
            false,
            ToggleReason::of('Withdrawn.'),
            new DateTimeImmutable('2026-11-26T10:00:00+00:00'),
        ));

        self::assertSame('off', $this->logger->lines[0]['context']['state']);
        self::assertTrue($this->logger->lines[0]['context']['before']);
        self::assertFalse($this->logger->lines[0]['context']['after']);
    }

    public function testACreationHasNoDefinitionBefore(): void
    {
        $this->auditor()->recordEdit(new EditRecord(
            FlagName::of('text.new-page'),
            'boss',
            null,
            new FlagDefinition(FlagName::of('text.new-page'), false),
            ToggleReason::of('A new page.'),
            new DateTimeImmutable('2026-11-26T10:00:00+00:00'),
        ));

        self::assertSame(LogLevel::NOTICE, $this->logger->lines[0]['level']);
        self::assertSame('Feature flag {flag} {change} by {actor}: {reason}', $this->logger->lines[0]['message']);
        self::assertSame('created', $this->logger->lines[0]['context']['change']);
        self::assertNull($this->logger->lines[0]['context']['before']);
        self::assertIsArray($this->logger->lines[0]['context']['after']);
        self::assertSame('text.new-page', $this->logger->lines[0]['context']['flag']);
        self::assertSame('boss', $this->logger->lines[0]['context']['actor']);
        self::assertSame('A new page.', $this->logger->lines[0]['context']['reason']);
        self::assertSame('2026-11-26T10:00:00+00:00', $this->logger->lines[0]['context']['at']);
    }

    public function testAnEditKeepsTheDefinitionBefore(): void
    {
        $definition = new FlagDefinition(FlagName::of('text.new-page'), false);
        $this->auditor()->recordEdit(new EditRecord(
            FlagName::of('text.new-page'),
            'boss',
            $definition,
            $definition,
            ToggleReason::of('Its owner.'),
            new DateTimeImmutable('2026-11-26T10:00:00+00:00'),
        ));

        self::assertSame('edited', $this->logger->lines[0]['context']['change']);
        self::assertIsArray($this->logger->lines[0]['context']['before']);
        self::assertSame($this->logger->lines[0]['context']['before'], $this->logger->lines[0]['context']['after']);
    }

    private function auditor(): LogToggleAuditor
    {
        return new LogToggleAuditor($this->logger);
    }
}
