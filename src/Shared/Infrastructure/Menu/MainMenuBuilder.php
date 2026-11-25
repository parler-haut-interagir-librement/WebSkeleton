<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Menu;

use App\Shared\Infrastructure\Http\Controller\HomeAction;
use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

/**
 * The main menu of the site, rendered in base.html.twig; its labels are translation keys, its links stay in the
 * language of the page.
 *
 * @see MainMenuBuilderTest
 */
final readonly class MainMenuBuilder
{
    public function __construct(
        private FactoryInterface $factory,
    ) {
    }

    #[AsMenuBuilder(name: 'main')]
    public function createMainMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('main');
        $menu->addChild('menu.home', ['route' => HomeAction::class]);
        $menu->addChild('menu.contact', ['route' => 'phil_contact_form']);

        return $menu;
    }
}
