<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Infrastructure\Http;

use App\Account\Infrastructure\Http\Controller\LogoutAction;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogoutAction::class)]
final class LogoutActionTest extends TestCase
{
    public function testTheActionIsNeverReached(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('The logout of the firewall answers this route.');

        new LogoutAction()();
    }
}
