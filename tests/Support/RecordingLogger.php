<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Override;
use Psr\Log\AbstractLogger;
use Stringable;

/**
 * A logger that keeps every line it is given, for the tests of the classes that log.
 */
final class RecordingLogger extends AbstractLogger
{
    /**
     * @var list<array{level: mixed, message: string, context: array<array-key, mixed>}>
     */
    public array $lines = [];

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $this->lines[] = ['level' => $level, 'message' => (string) $message, 'context' => $context];
    }
}
