<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * French and English, French by default. The locale of a page comes from its path, /fr or /en; the list of
 * languages is the parameter app.locales of config/services.php.
 *
 * @see https://symfony.com/doc/current/translation.html
 */
return App::config([
    'framework' => [
        'default_locale' => 'fr',
        'enabled_locales' => '%app.locales%',
        'set_content_language_from_locale' => true,
    ],
    'translation' => [
        'default_path' => '%kernel.project_dir%/translations',
        'fallbacks' => ['fr'],
    ],
]);
