<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\SignUpCommand;
use App\Account\Application\SignUpHandler;
use App\Account\Domain\Accounts;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignUpHandler::class)]
#[CoversClass(SignUpCommand::class)]
final class SignUpHandlerTest extends TestCase
{
    public function testTheAccountIsRegisteredThroughThePort(): void
    {
        $accounts = new class implements Accounts {
            /** @var list<array{string, string, string}> */
            public array $registered = [];

            public function register(string $username, string $email, string $plainPassword): void
            {
                $this->registered[] = [$username, $email, $plainPassword];
            }
        };

        new SignUpHandler($accounts)(new SignUpCommand('ada', 'ada@example.com', 'a long password'));

        self::assertSame([['ada', 'ada@example.com', 'a long password']], $accounts->registered);
    }
}
