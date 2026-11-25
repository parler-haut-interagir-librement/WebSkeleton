<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * phil/contact-form-bundle: the contact page, protected by phil/spam-protection-bundle. The sender is an address of
 * the site's own domain, or SPF and DMARC fail; the recipients receive the messages. Replace both example addresses.
 *
 * @see https://github.com/parler-haut-interagir-librement/contact-form-bundle
 */
return App::config([
    'phil_contact_form' => [
        'sender' => 'contact@example.com',
        'recipients' => ['info@example.com'],
        'subject_prefix' => '[Contact] ',
        'template' => 'contact/contact.html.twig',
    ],
]);
