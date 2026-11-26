<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\AccountView;
use App\Account\Application\AccountViews;
use App\Account\Application\ShowAccountHandler;
use App\Account\Application\ShowAccountQuery;
use App\Account\Domain\AccountId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShowAccountHandler::class)]
#[CoversClass(ShowAccountQuery::class)]
#[CoversClass(AccountView::class)]
final class ShowAccountHandlerTest extends TestCase
{
    public function testTheViewIsReadThroughThePort(): void
    {
        $id = AccountId::generate();
        $view = new AccountView($id, 'ada', ['ROLE_USER'], new DateTimeImmutable('2026-11-26 10:00:00'));

        self::assertSame($view, new ShowAccountHandler($this->views($view))(new ShowAccountQuery($id)));
    }

    public function testAnUnknownAccountHasNoView(): void
    {
        $known = AccountId::generate();
        $view = new AccountView($known, 'ada', ['ROLE_USER'], null);

        self::assertNull(new ShowAccountHandler($this->views($view))(new ShowAccountQuery(AccountId::generate())));
    }

    private function views(AccountView $view): AccountViews
    {
        return new readonly class($view) implements AccountViews {
            public function __construct(
                private AccountView $view,
            ) {
            }

            public function find(AccountId $id): ?AccountView
            {
                return $id->equals($this->view->id) ? $this->view : null;
            }
        };
    }
}
