<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * phil/accessibility-statement-bundle: no statement is written yet, so the page answers 404 and the footer does not
 * link to it; the site claims nothing no one has evaluated. Write the first version under "statement" once the site
 * is evaluated.
 *
 * @see https://github.com/parler-haut-interagir-librement/accessibility-statement-bundle
 */
return App::config([
    'phil_accessibility_statement' => [
        'layout' => 'layout/page.html.twig',
    ],
]);
