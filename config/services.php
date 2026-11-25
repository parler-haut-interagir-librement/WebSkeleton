<?php

// This file is the entry point to configure your own services.
// Files in the packages/ subdirectory configure your dependencies.

declare(strict_types=1);

use App\Text\Domain\TitleSlugger;
use App\Text\Infrastructure\AsciiTitleSlugger;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Parameters ——————————————————————————————————————————————————————————————
    $parameters = $containerConfigurator->parameters();

    // System parameters: https://symfony.com/doc/current/performance.html#dump-the-service-container-into-a-single-file
    $parameters->set('.container.dumper.inline_factories', true);

    // The languages of the site, the default one first: each is a path prefix (/fr, /en) and a translations file.
    // Add a language here and in translations/, and its pages exist.
    $locales = ['fr', 'en'];
    $parameters->set('app.locales', $locales)
        ->set('app.locale_requirement', implode('|', $locales));

    // Application parameters ——————————————————————————————————————————————————
    $sfVersion = substr(Kernel::VERSION, 0, 3); // minor Symfony version
    $description = <<<DESCRIPTION
A Symfony <b>$sfVersion</b> site skeleton: bilingual, organised in DDD, with the PHIL UX kit and the PHIL bundles a site needs.
DESCRIPTION;
    $parameters->set('brand', 'WebSkeleton')
        ->set('brand_html', '<b>Web</b><i>Skeleton</i>')
        ->set('website', 'https://github.com/parler-haut-interagir-librement/WebSkeleton')
        ->set('copyright_url', 'https://ph-il.ca')
        ->set('version', '8.2.0-RC1')
        ->set('description', $description);

    // Services ————————————————————————————————————————————————————————————————
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
        ->bind('array $locales', '%app.locales%')
    ;

    // src/<Context>/{Domain,Application,Infrastructure}, and src/Shared for what every context uses. In each context,
    // the domain holds plain PHP objects, never services; the application and the infrastructure are services, except
    // the commands and queries, which are messages.
    $services->load('App\\', __DIR__.'/../src')
        ->exclude([
            __DIR__.'/../src/*/Domain/',
            __DIR__.'/../src/*/Application/**/{*Query,*Command}.php',
            __DIR__.'/../src/Kernel.php',
        ]);

    // The ports of each domain, bound to their adapters in the infrastructure.
    $services->alias(TitleSlugger::class, AsciiTitleSlugger::class);
};
