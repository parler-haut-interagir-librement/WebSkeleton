<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Menu;

use App\Infrastructure\Menu\MainMenuBuilder;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

#[CoversClass(MainMenuBuilder::class)]
final class MainMenuBuilderTest extends KernelTestCase
{
    #[TestWith(['fr'])]
    #[TestWith(['en'])]
    public function testTheMainMenuStaysInTheLanguageOfThePage(string $locale): void
    {
        $router = self::getContainer()->get(RouterInterface::class);
        self::assertInstanceOf(RouterInterface::class, $router);
        $router->getContext()->setParameter('_locale', $locale);

        $menu = self::getContainer()->get('knp_menu.menu_provider');
        self::assertInstanceOf(MenuProviderInterface::class, $menu);
        self::assertTrue($menu->has('main'));

        $items = [];
        foreach ($menu->get('main')->getChildren() as $name => $item) {
            $items[$name] = $item->getUri();
        }

        self::assertSame([
            'menu.home' => '/'.$locale,
            'menu.contact' => '/'.$locale.'/contact',
        ], $items);
    }
}
