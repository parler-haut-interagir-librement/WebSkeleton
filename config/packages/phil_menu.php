<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The menus of phil/menu-bundle, built by the services that implement MenuBuilder: the main menu is
 * App\Shared\Infrastructure\Menu\MainMenuBuilder.
 *
 * @see vendor/phil/menu-bundle/doc/installation.md
 */
return App::config([
    'phil_menu' => [
        'allow_localhost' => false,
    ],
]);
