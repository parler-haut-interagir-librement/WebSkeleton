<?php

declare(strict_types=1);

namespace App\Tests\Integration\Shared\Infrastructure\ErrorTracking;

use App\Tests\Support\RecordingLogger;
use Phil\ErrorTracking\ErrorTracker;
use Phil\ErrorTracking\Occurrence;
use Phil\ErrorTrackingBundle\Application\UndeliveredAlerter;
use PHPUnit\Framework\Attributes\CoversNothing;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The alerts of config/packages/phil_error_tracking.yaml: the first event of a group dispatches an AlertErrorGroup
 * message on the command bus, handled at once while no transport is routed; with no chatter transport, the alert is
 * logged as a warning that names the group, and the capture never fails.
 */
#[CoversNothing]
final class ErrorAlertTest extends KernelTestCase
{
    use ResetDatabase;

    public function testTheFirstEventOfAGroupIsAlertedThroughTheCommandBus(): void
    {
        self::bootKernel();
        $logger = new RecordingLogger();
        self::getContainer()->set(UndeliveredAlerter::class, new UndeliveredAlerter($logger));

        $capture = self::getContainer()->get(ErrorTracker::class)->captureThrowable(new RuntimeException('The export failed'));

        self::assertNotNull($capture);
        self::assertSame(Occurrence::First, $capture->occurrence);
        self::assertSame([[
            'level' => LogLevel::WARNING,
            'message' => UndeliveredAlerter::MESSAGE,
            'context' => ['fingerprint' => $capture->fingerprint, 'occurrence' => Occurrence::First->value],
        ]], $logger->lines);
    }

    public function testARepeatedEventIsNotAlerted(): void
    {
        self::bootKernel();
        $logger = new RecordingLogger();
        self::getContainer()->set(UndeliveredAlerter::class, new UndeliveredAlerter($logger));
        $tracker = self::getContainer()->get(ErrorTracker::class);

        $tracker->captureThrowable(new RuntimeException('The export failed'));

        $capture = $tracker->captureThrowable(new RuntimeException('The export failed'));

        self::assertNotNull($capture);
        self::assertSame(Occurrence::Repeat, $capture->occurrence);
        self::assertCount(1, $logger->lines);
    }
}
