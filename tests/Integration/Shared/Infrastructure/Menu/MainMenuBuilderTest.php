<?php

declare(strict_types=1);

namespace App\Tests\Integration\Shared\Infrastructure\Menu;

use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use App\Shared\Infrastructure\Http\Controller\HomeAction;
use App\Shared\Infrastructure\Menu\MainMenuBuilder;
use Phil\MenuBundle\Application\MenuContext;
use Phil\MenuBundle\Application\Menus;
use Phil\MenuBundle\Domain\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[CoversClass(MainMenuBuilder::class)]
final class MainMenuBuilderTest extends KernelTestCase
{
    public function testAVisitorIsOfferedToSignInOrUp(): void
    {
        self::assertSame([
            'home' => [HomeAction::class, 'menu.home'],
            'contact' => ['phil_contact_form', 'menu.contact'],
            'login' => ['account_login', 'menu.login'],
            'signup' => ['account_signup', 'menu.signup'],
        ], $this->items());
    }

    public function testAMemberIsOfferedTheirAccount(): void
    {
        $this->signIn([]);

        self::assertSame(['home', 'contact', 'account'], array_keys($this->items()));
        self::assertSame(['account_show', 'menu.account'], $this->items()['account']);
    }

    public function testAnAdministratorIsOfferedTheAdministration(): void
    {
        $this->signIn(['ROLE_ADMIN']);

        self::assertSame(['home', 'contact', 'account', 'admin'], array_keys($this->items()));
        self::assertSame(['admin', 'menu.admin'], $this->items()['admin']);
    }

    /**
     * @param list<string> $roles
     */
    private function signIn(array $roles): void
    {
        $user = new User('ada', 'ada@example.com');
        $user->setRoles($roles);

        $tokenStorage = self::getContainer()->get(TokenStorageInterface::class);
        self::assertInstanceOf(TokenStorageInterface::class, $tokenStorage);
        $tokenStorage->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));
    }

    /**
     * @return array<string, array{string|null, string}>
     */
    private function items(): array
    {
        $menus = self::getContainer()->get(Menus::class);
        self::assertInstanceOf(Menus::class, $menus);
        $menu = $menus->get(MainMenuBuilder::NAME, new MenuContext());
        self::assertSame('messages', $menu->translationDomain);

        $items = [];
        foreach ($menu->items as $item) {
            self::assertInstanceOf(MenuItem::class, $item);
            $items[$item->key] = [$item->route, $item->label];
        }

        return $items;
    }
}
