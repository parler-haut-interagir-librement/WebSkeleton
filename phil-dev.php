<?php

declare(strict_types=1);

/*
 * What phil/dev-tools reads of this application: its paths and its exceptions to the shared configurations.
 * See vendor/phil/dev-tools/doc/configuration.md for every section.
 */
return [
    // Released on Packagist as phil/webskeleton: its CHANGELOG has the usual sections.
    'docs' => ['public' => true],
    'paths' => ['src', 'tests', 'fixtures', 'migrations', 'bin', 'public', 'importmap.php'],
    // The functional tests share one SQLite file: they run one after the other, with PHPUnit.
    'phpunit' => ['parallel' => false],
    // The layers of DDD, by bounded context: src/<Context>/{Domain,Application,Infrastructure}, and src/Shared for what
    // every context uses. Inside a context, the domain sees PHP, the Symfony components and the domain of
    // phil/ddd-bundle; the application sees the domain and the buses; the infrastructure sees everything it integrates.
    // Between contexts, a context sees the same layers of Shared, never another context; Shared sees no context.
    // A new context is one more name in $contexts below.
    'deptrac' => (static function (): array {
        $contexts = ['Account', 'Text'];
        $integrated = [
            'DefaultAllowed', 'PhilDddBundleDomain', 'PhilDddBundleApplication', 'PhilSitemap', 'AllowedDoctrine',
            'AllowedEasyCorpBundleEasyAdminBundle', 'AllowedSymfonyBridgeDoctrine', 'AllowedSymfonyBundleFrameworkBundle',
            'AllowedSymfonyBundleSecurityBundle', 'AllowedTwig', 'PhilDoctrineBehaviours', 'PhilErrorTrackingBundleInfrastructure',
            'PhilMenuBundleDomain', 'PhilMenuBundleApplication', 'PhilPrivacyBundleDomain', 'PhilPrivacyBundleApplication',
        ];
        /** @var array<string, string> $layers */
        $layers = [];
        /** @var array<string, list<string>> $ruleset */
        $ruleset = [];

        foreach (['Shared', ...$contexts] as $context) {
            $shared = 'Shared' === $context ? [] : ['SharedDomain'];
            $layers[$context.'Domain'] = '^App\\\\'.$context.'\\\\Domain\\\\.*';
            $layers[$context.'Application'] = '^App\\\\'.$context.'\\\\Application\\\\.*';
            $layers[$context.'Infrastructure'] = '^App\\\\'.$context.'\\\\Infrastructure\\\\.*';
            $ruleset[$context.'Domain'] = [...$shared, 'DefaultAllowed', 'PhilDddBundleDomain'];
            $ruleset[$context.'Application'] = [
                $context.'Domain', ...$shared, ...('Shared' === $context ? [] : ['SharedApplication']),
                'DefaultAllowed', 'PhilDddBundleDomain', 'PhilDddBundleApplication',
            ];
            $ruleset[$context.'Infrastructure'] = [
                $context.'Domain', $context.'Application',
                ...('Shared' === $context ? [] : ['SharedDomain', 'SharedApplication', 'SharedInfrastructure']),
                ...$integrated,
            ];
        }

        // The kernel, at the root of App.
        $layers['App'] = '^App\\\\[^\\\\]+$';
        $ruleset['App'] = ['DefaultAllowed', 'AllowedSymfonyBundleFrameworkBundle'];

        return [
            // Besides the Symfony components, the classes of src/ may use these libraries and bundles, and no other: a
            // new one is added here on purpose, after it is required by composer.json.
            'allow' => [
                '^Doctrine\\\\(Bundle\\\\DoctrineBundle|DBAL|ORM|Persistence)\\\\.*',
                '^EasyCorp\\\\Bundle\\\\EasyAdminBundle\\\\.*',
                '^Symfony\\\\Bridge\\\\Doctrine\\\\.*',
                '^Symfony\\\\Bundle\\\\FrameworkBundle\\\\.*',
                '^Symfony\\\\Bundle\\\\SecurityBundle\\\\.*',
                '^Twig\\\\.*',
            ],
            'layers' => $layers,
            'ruleset' => $ruleset,
        ];
    })(),
    // The Stimulus controllers and the scripts of the application; assets/vendor holds the packages of the importmap.
    'js' => ['paths' => ['assets/controllers', 'assets/app.js', 'assets/bootstrap.js']],
    // Used through the configuration, the templates, the front controller or the console, never by a class of src/.
    'unused' => ['ignore' => [
        'ext-iconv', 'ext-simplexml', 'doctrine/migrations', 'doctrine/doctrine-migrations-bundle', 'symfony/dotenv',
        'symfony/flex', 'symfony/monolog-bundle', 'symfony/runtime', 'symfony/ux-turbo', 'symfony/web-link',
        'nelmio/cors-bundle', 'phil/accessibility-statement-bundle', 'phil/browser-security-bundle',
        'phil/contact-form-bundle', 'phil/spam-protection-bundle', 'phil/ux-bundle', 'phil/ux-css',
        'phil/well-known-bundle', 'phil/doctrine-behaviours-bundle', 'phil/ux-easyadmin-bundle',
    ]],
];
