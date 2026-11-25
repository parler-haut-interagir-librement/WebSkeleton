<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * phil/legal-notices-bundle: the legal notice and the privacy policy, in French and English, their bodies in legal/.
 * Both are drafts: their pages answer 404 until the identity of the site is filled in and a version is published
 * (a published_at date, and state removed). bin/console phil:legal-notices:check reports what a body lacks.
 *
 * @see https://github.com/parler-haut-interagir-librement/legal-notices-bundle
 */
return App::config([
    'phil_legal_notices' => [
        'default_locale' => 'fr',
        // The fields a published document requires, each a placeholder of the bodies too: ${publisher_name}.
        'identity' => [
            'site_name' => null,
            'publisher_name' => null,
            'publisher_address' => null,
            'publication_director' => null,
            'contact_email' => null,
            'host_name' => null,
            'host_address' => null,
            'host_contact' => null,
            'dpo_name' => null,
            'dpo_contact' => null,
        ],
        'documents' => [
            'legal_notice' => [
                [
                    'number' => 1,
                    'format' => 'markdown',
                    'state' => 'draft',
                    'effective_at' => '2027-01-01',
                    // 'published_at' => '2027-01-01',
                    'files' => [
                        'fr' => '%kernel.project_dir%/legal/notice.fr.md',
                        'en' => '%kernel.project_dir%/legal/notice.en.md',
                    ],
                ],
            ],
            'privacy_policy' => [
                [
                    'number' => 1,
                    'format' => 'markdown',
                    'state' => 'draft',
                    'effective_at' => '2027-01-01',
                    // 'published_at' => '2027-01-01',
                    'files' => [
                        'fr' => '%kernel.project_dir%/legal/privacy.fr.md',
                        'en' => '%kernel.project_dir%/legal/privacy.en.md',
                    ],
                ],
            ],
        ],
        'consent' => [
            'categories' => [
                'necessary' => [
                    'necessary' => true,
                    'labels' => ['fr' => 'Nécessaires', 'en' => 'Necessary'],
                    'descriptions' => ['fr' => 'La session de votre visite.', 'en' => 'The session of your visit.'],
                    'cookies' => ['PHPSESSID'],
                ],
            ],
        ],
        'pages' => [
            'layout' => 'layout/page.html.twig',
        ],
    ],
]);
