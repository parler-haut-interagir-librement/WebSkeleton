<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

/**
 * The declared dependencies, checked against the code that uses them.
 */
return new Configuration()
    ->addPathToScan(__DIR__.'/src', isDev: false)
    ->addPathToScan(__DIR__.'/migrations', isDev: false)
    ->addPathToScan(__DIR__.'/tests', isDev: true)
    ->addPathToScan(__DIR__.'/fixtures', isDev: true)
    // Used through the configuration, the templates, the front controller or the console, never by a class of src/;
    // phil/monitoring-bundle only gives the interface phil/error-tracking-bundle 0.1.0-alpha needs: see phil-dev.php.
    ->ignoreErrorsOnPackages([
        'doctrine/doctrine-bundle',
        'doctrine/doctrine-migrations-bundle',
        'doctrine/persistence',
        'phil/accessibility-statement-bundle',
        'phil/browser-security-bundle',
        'phil/contact-form-bundle',
        'phil/cors-bundle',
        'phil/doctrine-behaviours-bundle',
        'phil/legal-notices-bundle',
        'phil/monitoring-bundle',
        'phil/sitemap-bundle',
        'phil/spam-protection-bundle',
        'phil/structured-data-bundle',
        'phil/ux-bundle',
        'phil/ux-css',
        'phil/ux-easyadmin-bundle',
        'phil/ux-kit',
        'phil/well-known-bundle',
        'symfony/asset',
        'symfony/asset-mapper',
        'symfony/console',
        'symfony/flex',
        'symfony/intl',
        'symfony/lock',
        'symfony/mailer',
        'symfony/messenger',
        'symfony/monolog-bundle',
        'symfony/rate-limiter',
        'symfony/runtime',
        'symfony/security-csrf',
        'symfony/stimulus-bundle',
        'symfony/translation',
        'symfony/twig-bundle',
        'symfony/ux-turbo',
        'symfony/ux-twig-component',
        'symfony/web-link',
        'symfony/yaml',
        'twig/extra-bundle',
        'twig/intl-extra',
    ], [ErrorType::UNUSED_DEPENDENCY])
    // The .env files are read by the front controller and the console through the runtime, the tests read them directly.
    ->ignoreErrorsOnPackage('symfony/dotenv', [ErrorType::PROD_DEPENDENCY_ONLY_IN_DEV])
    // Required by Symfony and its components, which the analysis does not follow.
    ->ignoreErrorsOnExtensions(['ext-ctype', 'ext-iconv', 'ext-simplexml', 'ext-xml'], [ErrorType::UNUSED_DEPENDENCY]);
