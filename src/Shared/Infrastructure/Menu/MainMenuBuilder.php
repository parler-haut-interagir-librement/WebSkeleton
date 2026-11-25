<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Menu;

use App\Shared\Infrastructure\Http\Controller\HomeAction;
use Override;
use Phil\MenuBundle\Application\MenuBuilder;
use Phil\MenuBundle\Application\MenuContext;
use Phil\MenuBundle\Domain\Menu;
use Phil\MenuBundle\Domain\MenuItem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * The main menu of the site, rendered in base.html.twig by phil/menu-bundle: its labels are translation keys, its
 * links stay in the language of the page. A visitor is offered to sign in or up; a member, their account; an
 * administrator, the administration too. The pages of the other contexts are named by their routes.
 *
 * @see MainMenuBuilderTest
 */
final readonly class MainMenuBuilder implements MenuBuilder
{
    public const string NAME = 'main';

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return self::NAME;
    }

    #[Override]
    public function build(MenuContext $context): Menu
    {
        $items = [
            MenuItem::of('home', 'menu.home')->withRoute(HomeAction::class),
            MenuItem::of('contact', 'menu.contact')->withRoute('phil_contact_form'),
        ];

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED')) {
            $items[] = MenuItem::of('login', 'menu.login')->withRoute('account_login');
            $items[] = MenuItem::of('signup', 'menu.signup')->withRoute('account_signup');
        } else {
            $items[] = MenuItem::of('account', 'menu.account')->withRoute('account_show');
        }

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $items[] = MenuItem::of('admin', 'menu.admin')->withRoute('admin');
        }

        return Menu::of(self::NAME, $items, translationDomain: 'messages');
    }
}
