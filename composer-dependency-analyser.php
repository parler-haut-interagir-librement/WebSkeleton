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
    // Used through the configuration, the templates, the front controller or the console, never by a class of src/;
    // Doctrine is ready for the entities of src/Infrastructure/Persistence/Doctrine/Entity/.
    ->ignoreErrorsOnPackages([
        'doctrine/dbal',
        'doctrine/doctrine-bundle',
        'doctrine/doctrine-migrations-bundle',
        'doctrine/migrations',
        'doctrine/orm',
        'doctrine/persistence',
        'knplabs/knp-menu-bundle',
        'nelmio/cors-bundle',
        'phil/accessibility-statement-bundle',
        'phil/browser-security-bundle',
        'phil/contact-form-bundle',
        'phil/legal-notices-bundle',
        'phil/sitemap-bundle',
        'phil/spam-protection-bundle',
        'phil/structured-data-bundle',
        'phil/ux-bundle',
        'phil/ux-css',
        'phil/ux-kit',
        'phil/well-known-bundle',
        'sentry/sentry-symfony',
        'symfony/asset',
        'symfony/asset-mapper',
        'symfony/console',
        'symfony/flex',
        'symfony/doctrine-bridge',
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
        'symfony/uid',
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
