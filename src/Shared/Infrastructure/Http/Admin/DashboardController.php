<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Admin;

use App\Shared\Infrastructure\Http\Controller\HomeAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Override;
use Phil\ErrorTrackingBundle\Infrastructure\EasyAdmin\ErrorTrackingAdmin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The administration, /admin, for ROLE_ADMIN only, in the theme of PHIL UX. Each context adds its CRUD controllers;
 * the menu links to them by the name EasyAdmin gives their routes, "admin_" and the name of the controller.
 *
 * @see DashboardControllerTest
 */
#[AdminDashboard(routePath: '/admin', routeName: self::ROUTE)]
#[IsGranted('ROLE_ADMIN')]
final class DashboardController extends AbstractDashboardController
{
    public const string ROUTE = 'admin';

    /**
     * The list of the feature flags, imported by config/routes/phil_feature_flags.yaml.
     */
    public const string FEATURE_FLAGS_ROUTE = 'phil_feature_flags_admin_list';

    /**
     * The page of the Messenger queues, imported by config/routes/phil_monitoring.yaml.
     */
    public const string QUEUES_ROUTE = 'phil_monitoring_queues';

    /**
     * @param list<string> $locales
     */
    public function __construct(
        #[Autowire(param: 'brand')]
        private readonly string $brand,
        #[Autowire(param: 'app.locales')]
        private readonly array $locales,
    ) {
    }

    #[Override]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->brand)
            ->setTranslationDomain('messages')
            ->setLocales($this->locales)
        ;
    }

    /**
     * @return iterable<MenuItemInterface>
     */
    #[Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('admin.menu.dashboard', 'fa fa-gauge');
        yield MenuItem::linkToRoute('admin.menu.users', 'fa fa-users', 'admin_user_index');
        yield ErrorTrackingAdmin::menuItem(self::ROUTE, 'admin.menu.errors');
        // The screens of phil/feature-flags-bundle, under /admin/flags, outside EasyAdmin.
        yield MenuItem::linkToUrl('admin.menu.flags', 'fa fa-toggle-on', $this->generateUrl(self::FEATURE_FLAGS_ROUTE));
        // The page of the Messenger queues of phil/monitoring-bundle, under /admin/queues, outside EasyAdmin.
        yield MenuItem::linkToUrl('admin.menu.queues', 'fa fa-list-check', $this->generateUrl(self::QUEUES_ROUTE));
        yield MenuItem::linkToUrl('admin.menu.site', 'fa fa-house', $this->generateUrl(HomeAction::class));
    }

    /**
     * The menu of the administrator, without the sign-out link of EasyAdmin, which is a GET: the site signs out by POST
     * only, from its own pages.
     */
    #[Override]
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setMenuItems([MenuItem::linkToUrl('admin.menu.account', 'fa fa-user', $this->generateUrl('account_show'))]);
    }
}
