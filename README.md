# WebSkeleton 🦴

[![Latest Version](https://img.shields.io/packagist/v/phil/webskeleton.svg?style=flat-square)](https://packagist.org/packages/phil/webskeleton)
[![PHP Version Require](https://poser.pugx.org/phil/webskeleton/require/php?style=flat-square)](https://packagist.org/packages/phil/webskeleton)
[![Symfony Version](https://img.shields.io/badge/Symfony-8.2-black?logo=symfony)](https://symfony.com/8)
[![PHPStan level](https://img.shields.io/badge/PHPStan-level%20max-success?style=flat-square)](phpstan.neon)
[![Software License](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status (GitHub)](https://img.shields.io/github/actions/workflow/status/parler-haut-interagir-librement/WebSkeleton/symfony.yml?branch=main&style=flat-square)](https://github.com/parler-haut-interagir-librement/WebSkeleton/actions?query=workflow%3ASymfony+branch%3Amain)

## About 🖋

“**Proclaim** *Honestly*, **Interact** *Liberally*” **Web***Skeleton* is the base of a [Symfony 8.2](https://symfony.com/8)
site: French and English, organised in domain-driven design (Domain, Application, Infrastructure), dressed by the PHIL
UX kit, with member accounts, an EasyAdmin administration and the PHIL bundles a public site needs from its first day —
security headers, legal notice, privacy policy, privacy choices of the members, accessibility statement, contact form,
error tracking, sitemap, `security.txt` and structured data —, and the same tests and quality gates as the other
templates of PHIL.

WebSkeleton is built on [WebSymfony](https://github.com/parler-haut-interagir-librement/WebSymfony), itself built on
[MicroSymfony](https://github.com/parler-haut-interagir-librement/MicroSymfony), the PHIL version of
[strangebuzz/MicroSymfony](https://github.com/strangebuzz/MicroSymfony), created and maintained by
[COil](https://github.com/COil) of [Strangebuzz](https://www.strangebuzz.com). It is the first of these templates with
the PHIL tools: phil-ux in place of Pico CSS, the PHIL UX kit, and the PHIL bundles.

## Table of Contents 📖

* [Demo](#demo-)
* [Quick-start](#quick-start-)
* [Requirements](#requirements-)
* [Stack](#stack-)
* [What it adds to WebSymfony](#what-it-adds-to-websymfony-)
* [Configuration](#configuration-)
* [Languages](#languages-)
* [Domain-driven design](#domain-driven-design-)
* [Accounts and administration](#accounts-and-administration-)
* [Feature flags and circuit breakers](#feature-flags-and-circuit-breakers-)
* [Accessibility](#accessibility-)
* [Before going live](#before-going-live-)
* [Optional steps](#optional-steps-)
* [Tasks](#tasks-)
* [Quality gates](#quality-gates-)
* [What it doesn't ship](#what-it-doesnt-ship-)
* [References](#references-)
* [Others “Proclaim Honestly, Interact Liberally” Skeletons](#others-proclaim-honestly-interact-liberally-skeletons-)
* [Contributing](#contributing-)
* [Security](#security-)
* [Credits](#credits-)
* [License](#license-)
* [Built with WebSkeleton](#built-with-webskeleton-)

## Demo 🌈

A demo of WebSkeleton will run in the browser on [ph-il.ca](https://ph-il.ca), with the other templates of PHIL.

## Quick-start ⚡

Create a new project from the latest release:

    composer create-project phil/webskeleton my-site && cd my-site

The repository is also a GitHub template repository: the "Use this template" button creates a new repository from the
main branch, whose commits all pass the continuous integration.

### With the Symfony binary 🕸️

You need the [Symfony binary](https://symfony.com/download#step-1-install-symfony-cli) and
[Castor](https://castor.jolicode.com/getting-started/installation/). The database is a SQLite file in `var/`:

    castor start

The task writes a random `APP_SECRET` to `.env.local` when it holds none, prepares the database and starts the server.
Open [https://127.0.0.1:8000](https://127.0.0.1:8000) (if port 8000 is free): `/` sends you to `/fr` or `/en`, after
the language of your browser. `castor stop` stops the server and deletes the database.

### With FrankenPHP and PostgreSQL 🧟

You need [Docker](https://www.docker.com/). `compose.yaml` serves the application with
[FrankenPHP](https://frankenphp.dev/) in worker mode, which boots the kernel once and serves request after request,
and restarts its workers when a file changes; the database is PostgreSQL 17, and
[Mailpit](https://mailpit.axllent.org/) catches the messages of the contact form:

    castor secret && castor up

Open [https://localhost](https://localhost). `castor down` stops the services. The image of `docker/Dockerfile` is
for development: a site built from this template brings its own deployment.

### With MySQL 🐬

MySQL is the other database the template supports. In `compose.yaml`, replace the `database` service with a
`mysql:8.4` image and set `DATABASE_URL` to:

    mysql://app:app@database:3306/app?serverVersion=8.4&charset=utf8mb4

With the Symfony binary, set the same variable in `.env.local`, with the host of your server.

## Requirements ⚙

* [PHP 8.5](https://www.php.net/releases/8.5/en.php) or later, with the extensions of `composer.json`, `intl` and
  `sodium`, and [Castor](https://castor.jolicode.com)
* The [Symfony binary](https://symfony.com/download), or [Docker](https://www.docker.com)
* The [Xdebug](https://xdebug.org/) or [PCOV](https://github.com/krakjoe/pcov) extension for the code coverage report

## Stack 🔗

* [PHP 8.5](https://www.php.net/releases/8.5/en.php)
* [Symfony 8.2](https://symfony.com/8), from its release candidate, with [Messenger](https://symfony.com/doc/current/messenger.html)
  carrying the commands and queries
* [Doctrine ORM 3 (DBAL 4)](https://www.doctrine-project.org), on SQLite, PostgreSQL or MySQL
* [Symfony Security](https://symfony.com/doc/current/security.html) for the member accounts, and
  [EasyAdmin 5](https://symfony.com/bundles/EasyAdminBundle/current/index.html) for the administration
* [Twig 3](https://twig.symfony.com) and [Twig components](https://symfony.com/bundles/ux-twig-component/current/index.html)
* [AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html), [Stimulus 3.2](https://stimulus.hotwired.dev/)
  and [Turbo 8](https://turbo.hotwired.dev/), without Node.js
* [phil-ux](https://github.com/parler-haut-interagir-librement/ux-css), the stylesheet of PHIL, light and dark, and the
  recipes of the [PHIL UX kit](https://github.com/parler-haut-interagir-librement/ux-kit)
* [PHPUnit 13](https://phpunit.de)
* [FrankenPHP](https://frankenphp.dev/) in worker mode, through Docker Compose

## What it adds to WebSymfony 🚀

| Feature | Where |
|---|---|
| French and English, every page under `/fr` or `/en`, French by default | [routes.yaml](config/routes.yaml), [services.yaml](config/services.yaml), [translations](translations) |
| Domain, Application and Infrastructure layers in each bounded context, with the buses of `phil/ddd-bundle` | [src](src), [messenger.yaml](config/packages/messenger.yaml) |
| Member accounts: sign-up, sign-in, the account page and the sign-out, under `/fr` and `/en`, in the context `Account` | [src/Account](src/Account), [security.yaml](config/packages/security.yaml) |
| The administration, EasyAdmin in the theme of PHIL UX by `phil/ux-easyadmin-bundle`, for `ROLE_ADMIN` | [DashboardController](src/Shared/Infrastructure/Http/Admin/DashboardController.php), [phil_ux_easy_admin.yaml](config/packages/phil_ux_easy_admin.yaml) |
| The privacy choices of each member — the export of their data, the deletion of their account, the consent — and the redaction of the logs, by `phil/privacy-bundle` | [phil_privacy.yaml](config/packages/phil_privacy.yaml), [AccountPersonalData](src/Account/Infrastructure/Privacy/AccountPersonalData.php) |
| The errors of the site kept in its own database and shown in the administration, by `phil/error-tracking-bundle` | [phil_error_tracking.yaml](config/packages/phil_error_tracking.yaml) |
| Feature flags, off until an administrator turns them on under `/admin/flags`, by `phil/feature-flags-bundle` | [phil_feature_flags.yaml](config/packages/phil_feature_flags.yaml), [doc](doc/feature-flags-and-circuit-breakers.md) |
| A circuit breaker around each third party the site calls, by `phil/circuit-breaker-bundle` | [phil_circuit_breaker.yaml](config/packages/phil_circuit_breaker.yaml), [doc](doc/feature-flags-and-circuit-breakers.md) |
| The main menu, by `phil/menu-bundle` | [MainMenuBuilder](src/Shared/Infrastructure/Menu/MainMenuBuilder.php) |
| The creation and update dates of the accounts, by `phil/doctrine-behaviours-bundle` | [User](src/Account/Infrastructure/Persistence/Doctrine/Entity/User.php), [phil_doctrine_behaviours.yaml](config/packages/phil_doctrine_behaviours.yaml) |
| phil-ux and the recipes of the PHIL UX kit in `templates/components/` | [base.html.twig](templates/base.html.twig), [components](templates/components) |
| A Content Security Policy with nonces and the security headers, by `phil/browser-security-bundle` | [phil_browser_security.yaml](config/packages/phil_browser_security.yaml) |
| The legal notice and the privacy policy, in both languages, by `phil/legal-notices-bundle` | [phil_legal_notices.yaml](config/packages/phil_legal_notices.yaml), [legal](legal) |
| The accessibility statement, by `phil/accessibility-statement-bundle` | [phil_accessibility_statement.yaml](config/packages/phil_accessibility_statement.yaml) |
| A contact form protected against spam, by `phil/contact-form-bundle` and `phil/spam-protection-bundle` | [phil_contact_form.yaml](config/packages/phil_contact_form.yaml) |
| `/sitemap.xml`, every page in every language with its alternates, by `phil/sitemap-bundle` | [LocalizedPagesUrlProvider](src/Shared/Infrastructure/Sitemap/LocalizedPagesUrlProvider.php) |
| The cross-origin rules of the JSON endpoints under `/api/`, for the origins that `CORS_ALLOW_ORIGIN` matches, by `phil/cors-bundle` | [phil_cors.yaml](config/packages/phil_cors.yaml), [CorsTest](tests/Functional/CorsTest.php) |
| `/.well-known/security.txt`, by `phil/well-known-bundle` | [phil_well_known.yaml](config/packages/phil_well_known.yaml) |
| The JSON-LD graph of each page, by `phil/structured-data-bundle` | [phil_structured_data.yaml](config/packages/phil_structured_data.yaml) |
| Every page checked for accessibility, in both languages, by `phil/accessibility-check`, and every route by the smoke test of `phil/dev-tools` | [AccessibilityTest](tests/Functional/AccessibilityTest.php), [RouteSmokeTest](tests/Functional/RouteSmokeTest.php) |

From WebSymfony stay the PHIL identity (logo, copyright of Parler Haut, Interagir Librement, light and dark themes),
the member accounts and their administration, the cross-origin rules on `/api/`, the five test suites, Castor, the
quality gates and Docker Compose. The PHIL bundles take the place of KnpMenu, of Sentry, of NelmioCorsBundle and of the
Doctrine extensions of Gedmo.

Every PHIL package comes from Packagist. A package of PHIL not published yet is taken by a Composer `path` repository
to a local copy, until its release: today `phil/dev-tools` 0.2.1-alpha, from `../dev-tools`, a clone of its repository
next to the template's, and `phil/cors-bundle` 0.1.0-alpha, from `../phil/packages/cors-bundle`, in a clone of the PHIL
monorepository next to it. Both `repositories` entries of `composer.json` go at the release of 8.2.0-RC2, once these
two versions are on Packagist.
`phil/monitoring-bundle` is required without being registered: the configuration of `phil/error-tracking-bundle`
0.1.0-alpha reads a class that implements one of its interfaces.

## Configuration 🗂️

The configuration of Symfony and of the bundles is in YAML, under `config/`: `config/packages/*.yaml`, with
`when@dev`, `when@test` and `when@prod` blocks for what changes by environment, `config/routes.yaml`,
`config/routes/*.yaml` and `config/services.yaml`. The PHIL bundles are configured in the same format, as their Flex
recipes write it, and a recipe adds its own files next to them. Only `config/bundles.php` and `config/preload.php` stay
PHP. `castor lint:yaml` checks every file of `config/`, and `castor lint:container` checks the container they build.

```yaml
# config/packages/phil_sitemap.yaml
phil_sitemap:
    default_section: main

when@dev: &dev
    phil_sitemap:
        allow_localhost: true

when@test: *dev
```

## Languages 🌍

Every page lives under the prefix of its language: `/fr`, `/en`. French is the default: `/` sends a visitor to the
language their browser prefers among those of the site, French when it prefers none of them. The layout links each
page to its other languages, and the sitemap lists them as alternates.

The languages are the list `app.locales` in [config/services.yaml](config/services.yaml), the default one first,
and `app.locale_requirement` next to it, the same codes joined by `|` for the route prefix. To add Spanish:

1. add `es` to `app.locales` and to `app.locale_requirement`, `fr|en|es`;
2. copy `translations/messages.en.yaml` to `translations/messages.es.yaml` and translate it;
3. give the legal documents their `es` body in `legal/` and in
   [phil_legal_notices.yaml](config/packages/phil_legal_notices.yaml).

The pages of `/es` then exist, with the routes of the PHIL bundles under the same prefix.

## Domain-driven design 🧭

`src/` is organised by bounded context: each context has its own three layers, `src/<Context>/Domain`,
`src/<Context>/Application` and `src/<Context>/Infrastructure`, and `src/Shared` holds what every context uses.
Deptrac checks both kinds of rules (`castor lint:architecture`, rules in `phil-dev.php`).

Inside a context, the layers:

| Layer | Holds | May use |
|---|---|---|
| `Domain` | Value objects, entities of the model, ports (interfaces): plain PHP, never services | PHP, the Symfony components, the domain of `phil/ddd-bundle` |
| `Application` | Commands, queries and their handlers | The domain, the buses of `phil/ddd-bundle` |
| `Infrastructure` | Controllers, adapters of the ports, Doctrine entities | Everything it integrates |

Between contexts, the isolation: a context may use the same layer of `src/Shared` and the layers below it, never
another context; `src/Shared` uses no context, and names the pages of the others by their routes. Here,
`src/Shared/Infrastructure` holds the controllers of the site pages, the dashboard of the administration, the menu,
the sitemap provider, Twig and the Doctrine entities every context shares.

```text
src/
├── Kernel.php
├── Account/
│   ├── Domain/                 # Role, Accounts (the port)
│   ├── Application/            # SignUpCommand, SignUpHandler
│   └── Infrastructure/
│       ├── Form/               # the sign-up form
│       ├── Http/Admin/         # UserCrudController
│       ├── Http/Controller/    # login, signup, account, logout
│       ├── Persistence/Doctrine/  # DoctrineAccounts, Entity/User
│       └── Privacy/            # AccountPersonalData, for phil/privacy-bundle
├── Shared/
│   ├── Domain/
│   ├── Application/
│   └── Infrastructure/
│       ├── Http/Admin/         # DashboardController, /admin
│       ├── Http/Controller/    # pages, under /fr and /en
│       ├── Http/Root/          # "/", which sends to the visitor's language
│       ├── Menu/
│       ├── Persistence/Doctrine/Entity/
│       ├── Sitemap/
│       └── Twig/
└── Text/
    ├── Domain/                 # Slug, TitleSlugger
    ├── Application/            # SlugifyTitleQuery, SlugifyTitleHandler
    └── Infrastructure/
        ├── AsciiTitleSlugger.php
        ├── Http/Api/           # /api/slugify
        └── Persistence/Doctrine/Entity/
```

The example context, `Text`, runs through the three layers: [Slug](src/Text/Domain/Slug.php) is a value object that
validates itself and throws the `InvalidValue` of `phil/ddd-bundle`; [TitleSlugger](src/Text/Domain/TitleSlugger.php)
is its port; [SlugifyTitleQuery](src/Text/Application/SlugifyTitleQuery.php) and its handler answer on the query bus;
[AsciiTitleSlugger](src/Text/Infrastructure/AsciiTitleSlugger.php) is the adapter on Symfony's slugger, bound to the
port in [services.yaml](config/services.yaml); [SlugifyAction](src/Text/Infrastructure/Http/Api/SlugifyAction.php) asks
the bus and answers JSON, which the home page calls through a Stimulus controller.

The command bus runs in a Doctrine transaction, the query bus does not; both are synchronous until a message is routed
to a transport in [messenger.yaml](config/packages/messenger.yaml). Messages carry identifiers, never whole objects.

### Adding a context

For a context named `Catalog`:

1. **The folders**: `src/Catalog/Domain`, `src/Catalog/Application` and `src/Catalog/Infrastructure`, with the
   namespaces `App\Catalog\Domain`, `App\Catalog\Application` and `App\Catalog\Infrastructure`; its tests mirror them
   under `tests/Unit/Catalog/` and `tests/Integration/Catalog/`.
2. **The services**: nothing to add for the folders, since [services.yaml](config/services.yaml) loads `src/` and leaves
   out every `src/*/Domain/` and the commands and queries of every `src/*/Application/`. Each port of the domain gets
   its alias to its adapter there, next to `TitleSlugger`.
3. **The routes**: nothing to add either: [routes.yaml](config/routes.yaml) finds the controllers of every context in
   `Infrastructure/Http/Controller` (under the language prefix), `Infrastructure/Http/Api` (under `/api`) and
   `Infrastructure/Http/Root`.
4. **The Doctrine mapping**: a `Catalog` mapping in [doctrine.yaml](config/packages/doctrine.yaml), on
   `src/Catalog/Infrastructure/Persistence/Doctrine/Entity`, like the `Text` one.
5. **Deptrac**: `'Catalog'` added to `$contexts` in `phil-dev.php`, which gives it its three layers and both kinds of
   rules.

## Accounts and administration 🔐

The context `Account` holds the member accounts. [User](src/Account/Infrastructure/Persistence/Doctrine/Entity/User.php)
is a Doctrine entity of its infrastructure, identified by a UUID, which is also how `phil/privacy-bundle` knows the
member, never by their email address. The sign-up goes through the command bus: `SignUpCommand`, its handler, and the
port `Accounts` of the domain, whose adapter hashes the password and keeps the account.

| Page | Path | What it does |
|---|---|---|
| Sign-up | `/fr/signup`, `/en/signup` | A user name, an email address and a password of 12 characters or more, typed twice; both the name and the address are unique. A valid form creates the account and signs it in. |
| Sign-in | `/fr/login`, `/en/login` | The form login of Symfony, with a CSRF token; five failed attempts a minute per user name and address are allowed. |
| Account | `/fr/account`, `/en/account` | The account of the member, a link to their privacy choices and the button that signs them out. |
| Sign-out | `/fr/logout`, `/en/logout` | By POST with a CSRF token only: a link or an image never signs anyone out. |
| Privacy choices | `/fr/privacy-choices`, `/en/privacy-choices` | The export of the member's data, the deletion of their account after thirty days, the consent; the deletion asks for the password again when the session is not recent. |
| Administration | `/admin` | For `ROLE_ADMIN` only, which includes `ROLE_USER`: the accounts, listed and shown, never created nor edited there, the errors of the site, and the feature flags under `/admin/flags`. |

The firewall checks the account again each time it is refreshed from the session (`user_checker_on_refresh: true`), so
a changed or disabled account is signed out on its next request. The session keeps a crc32c hash of the password hash,
never the hash itself; a changed password signs the other sessions out. Impersonation stays off; if a site turns it
on, only by POST with a CSRF token, never by a query parameter.

`castor load-fixtures` resets the SQLite database and loads two named accounts and ten more members, for development
only:

| User name | Password | Roles |
|---|---|---|
| `admin` | `admin-password` | `ROLE_ADMIN` |
| `member` | `member-password` | `ROLE_USER` |

A context adds its own pages to the administration with a CRUD controller in its `Infrastructure/Http/Admin/` folder,
and a link in the menu of the [DashboardController](src/Shared/Infrastructure/Http/Admin/DashboardController.php), by
the name EasyAdmin gives its routes. Its personal data take part in the export and the erasure of a member with a
`PersonalDataExporter` and a `PersonalDataEraser`, as `AccountPersonalData` does.

To sign members in with an identity provider through OpenID Connect instead of passwords, replace the form login with
[`phil/identity-client-bundle`](https://github.com/parler-haut-interagir-librement/identity-client-bundle), as its
installation guide shows: the firewall keeps `user_checker_on_refresh: true`, and the sign-up, the passwords and their
fixtures go.

## Feature flags and circuit breakers 🎚️

A new feature ships behind a flag of [`phil/feature-flags-bundle`](https://github.com/parler-haut-interagir-librement/feature-flags-bundle),
declared off in [phil_feature_flags.yaml](config/packages/phil_feature_flags.yaml) and turned on by an administrator
under `/admin/flags`, with a reason written to the `feature_flags` log channel; the states live in the database. A
third party that fails is no longer called for a while, thanks to a circuit of
[`phil/circuit-breaker-bundle`](https://github.com/parler-haut-interagir-librement/circuit-breaker-bundle) around its
HTTP client, declared in [phil_circuit_breaker.yaml](config/packages/phil_circuit_breaker.yaml); each circuit also has
a flag that forces it open. The examples of the template are the flag `text.slug-page`, which guards the page
`/fr/slug` and its link on the home page, and the circuit `http.example`, around the scoped client `example.client`.
What is wired, where a flag is read in the layers, and how to add a flag or a circuit:
[doc/feature-flags-and-circuit-breakers.md](doc/feature-flags-and-circuit-breakers.md).

## Accessibility ♿

Every page of the site is checked by [`phil/accessibility-check`](https://github.com/parler-haut-interagir-librement/accessibility-check)
on each run of the tests: [AccessibilityTest](tests/Functional/AccessibilityTest.php) requests the pages of the route
smoke test, in French and in English, as a visitor and as an administrator, and fails on an accessibility error; a
warning is written out and passes. A route or a rule is left out in the test itself, with its reason.

To check what the authors of a site publish — a validator constraint on a form, a guard that holds back a publication
in a workflow — see [doc/accessibility.md](doc/accessibility.md). Nothing of it is wired by default.

## Before going live 🚀

The template starts with nothing that claims what no one has checked. Before a site is public:

1. **The secret**: set `APP_SECRET` in the environment of the server, or in the Symfony secrets.
2. **The legal documents**: fill the identity of the site in
   [phil_legal_notices.yaml](config/packages/phil_legal_notices.yaml), adapt the bodies in `legal/`, then publish each
   version (a `published_at` date, `state` removed). Until then `/fr/legal` and `/fr/privacy` answer 404.
   `bin/console phil:legal-notices:check` reports what a body lacks.
3. **The accessibility statement**: write its first version once the site is evaluated; until then its page answers
   404 and the footer does not link to it.
4. **The contact form**: replace the example sender and recipient in
   [phil_contact_form.yaml](config/packages/phil_contact_form.yaml) and set `MAILER_DSN`.
5. **`security.txt`**: replace the contact in [phil_well_known.yaml](config/packages/phil_well_known.yaml) and push its
   expiry date back, at most a year ahead.
6. **Several servers**: give the lock (`LOCK_DSN`), `cache.app` and `cache.circuits` a store every server shares, and
   remove `single_server` from [phil_browser_security.yaml](config/packages/phil_browser_security.yaml),
   [phil_error_tracking.yaml](config/packages/phil_error_tracking.yaml) and
   [phil_circuit_breaker.yaml](config/packages/phil_circuit_breaker.yaml).
7. **The accounts**: never load the fixtures, whose passwords are public; create the first administrator by signing up,
   then give the account `ROLE_ADMIN` in the database.
8. **Personal data**: set `APP_PUBLIC_ORIGIN` to the address of the site, run the account deletions that are due with
   `bin/console messenger:consume scheduler_phil_privacy` (with `symfony/scheduler`) or `bin/console
   phil:privacy:deletions` from cron, and declare in [config/privacy/cookies.yaml](config/privacy/cookies.yaml) every
   cookie the site adds.
9. **The errors**: set `APP_RELEASE` to the version that runs, and purge the old events with
   `bin/console phil:error-tracking:purge` from cron.
10. **The third parties**: rename the example client `example.client` and its circuit `http.example` after the third
    party the site calls, or remove them with `EXAMPLE_API_URL`; keep the lines of the `feature_flags` log channel,
    the audit trail of the flags.

## Optional steps 🧩

### Sign-in by OpenID Connect

The accounts sign in with a password by default. To sign them in with the identity provider of your choice:

    composer require phil/identity-client-bundle

Replace `form_login` in [security.yaml](config/packages/security.yaml) with the authenticator of the bundle, keep
`user_checker_on_refresh: true`, and import the routes of `oidc_login` as the
[installation guide of the bundle](https://github.com/parler-haut-interagir-librement/identity-client-bundle/blob/main/doc/installation.md)
shows.

### Tailwind instead of PHIL UX CSS

The template is dressed by phil-ux and the PHIL UX kit. A site that prefers Tailwind CSS takes the Shadcn kit of the
Symfony UX Toolkit and `phil/ux-kit-shadcn`, the PHIL UX recipes in Tailwind under the same names and props, with
`symfonycasts/tailwind-bundle` on AssetMapper: [doc/tailwind.md](doc/tailwind.md) gives what to remove, what to add,
the commands and what changes in the templates.

### More recipes of the PHIL UX kit

The recipes in `templates/components/` — `Badge`, `Button`, `Card`, `Table`, `ThemeSwitcher` — are copies the site
owns. Others come from the kit with the Symfony UX Toolkit:

    bin/console ux:install accordion --kit=github.com/parler-haut-interagir-librement/ux-kit

## Tasks 🧰

`castor` lists every task. The tests, the coverage, the lints, the fixers, the local continuous integration and the git
hooks are the common tasks of [`phil/dev-tools`](https://packagist.org/packages/phil/dev-tools), which
[castor.php](castor.php) imports from `vendor/phil/dev-tools/resources/castor/tasks.php` and sets with its
`PHIL_CASTOR` constant; the file keeps the template's own tasks — servers, database, environments, `test:schema` and
`lint:doctrine`. Run `composer install` before the first task. The main ones:

| Task | What it does |
|---|---|
| `castor start`, `castor stop` | The Symfony binary's web server with SQLite and its migrations. |
| `castor up`, `castor down` | FrankenPHP in worker mode, PostgreSQL and Mailpit, with Docker Compose. |
| `castor secret` | A random `APP_SECRET` in `.env.local`, unless it holds one. |
| `castor reset-database` | The SQLite database reset and migrated. |
| `castor load-fixtures` | The SQLite database reset and migrated, with the accounts `admin` and `member` and ten more. |
| `castor test` | Every PHPUnit suite; `--filter` and `--options` pass on to PHPUnit. |
| `castor test:unit`, `test:integration`, `test:api`, `test:functional`, `test:e2e` | One suite; `castor test:suite <name>` runs any suite of `phpunit.dist.xml`. |
| `castor test:next` | The tests on the next PHP version, in its Docker image. |
| `castor test:schema` | The migrations on a fresh SQLite database, then its schema compared with the Doctrine mapping. |
| `castor coverage` | The HTML coverage report in `var/coverage/`, and its threshold. |
| `castor infection` | Mutation testing. |
| `castor lint` | Every lint and the static analysis, then `lint:doctrine`. |
| `castor fix` | Rector, PHP-CS-Fixer, Twig-CS-Fixer, Biome and composer-normalize. |
| `castor ci` | The continuous integration, locally: coverage, every lint, then `test:schema`. |
| `castor hooks` | The git hooks: the fast checks on the staged files before each commit. |

## Quality gates 🚦

The checks come from [`phil/dev-tools`](https://packagist.org/packages/phil/dev-tools), the shared tooling of PHIL,
adapted to an application. Each tool is installed in its own `vendor-bin/<tool>/` directory by the
[Composer bin plugin](https://github.com/bamarni/composer-bin-plugin), so that its dependencies never meet the
application's. The thin configuration files at the root (`phpstan.neon`, `rector.php`, `.php-cs-fixer.dist.php`,
`arkitect.php`) call the shared ones; `phil-dev.php` holds what is particular to the application, each exception with
its reason.

| Task | Checks |
|---|---|
| `castor lint:composer` | The dependency analysis (a class used without its package required), `composer audit`, composer-unused and composer-normalize. |
| `castor lint:php` | PHP-CS-Fixer: `@Symfony`, `@Symfony:risky`, strict types and strict comparisons. |
| `castor lint:twig` | `lint:twig`, then the code style of the templates. |
| `castor lint:js` | Biome on the Stimulus controllers. |
| `castor lint:yaml`, `lint:container`, `lint:doctrine` | The YAML files (`config/` among them), the container and the Doctrine mapping. |
| `castor stan` | PHPStan at the maximum level, with type coverage at 100 %, a cognitive complexity limit and forbidden calls. |
| `castor lint:rector` | Rector has nothing left to change. |
| `castor lint:architecture` | Arkitect on the shape of the classes; Deptrac on the layers of DDD and the libraries each may use. |

## What it doesn't ship 🙅

* No deployment file: each site brings its own.
* No sign-in by an identity provider: it is an [optional step](#optional-steps-).
* No published legal document and no accessibility statement: the site publishes them once they say something true.
* No Node.js build: AssetMapper serves the JavaScript and phil-ux as they are.

## References 📚

* [Introducing the MicroSymfony application template](https://www.strangebuzz.com/en/blog/introducing-the-microsymfony-application-template) (strangebuzz.com)
* [MicroSymfony 8: ready for the LTS promise](https://www.strangebuzz.com/en/blog/microsymfony-8-ready-for-the-lts-promise) (strangebuzz.com)
* [Organizing your Symfony project tests](https://www.strangebuzz.com/en/blog/organizing-your-symfony-project-tests) (strangebuzz.com)
* [A better ADR pattern for your Symfony controllers](https://www.strangebuzz.com/en/blog/a-better-adr-pattern-for-your-symfony-controllers) (strangebuzz.com)
* [Castor, a journey across the sea of task runners](https://jolicode.com/blog/castor-a-journey-across-the-sea-of-task-runners) (jolicode.com)
* [Messenger: sync and queued message handling](https://symfony.com/doc/current/messenger.html) (symfony.com)
* [Localized routes](https://symfony.com/doc/current/routing.html#localized-routes-i18n) (symfony.com)
* [The Symfony Framework Best Practices](https://symfony.com/doc/current/best_practices.html) (symfony.com)

## Others “**Proclaim** Honestly, **Interact** Liberally” Skeletons 🩻

* [MicroSymfony](https://github.com/parler-haut-interagir-librement/MicroSymfony) (`phil/microsymfony`): the micro
  template, with no database, Pico CSS, Stimulus and Turbo, Castor, the tests and quality gates.
* [WebSymfony](https://github.com/parler-haut-interagir-librement/WebSymfony) (`phil/websymfony`): MicroSymfony and
  what a web application needs — Doctrine, member accounts, security headers, an administration, English and French —,
  in the usual layout of Symfony and on Pico CSS.
* [ApiSymfony](https://github.com/parler-haut-interagir-librement/ApiSymfony) (`phil/apisymfony`): an API with
  [API Platform](https://api-platform.com), Castor, the tests and quality gates.

## Contributing 🤝

See [CONTRIBUTING](CONTRIBUTING.md) and the [Code of Conduct](CODE_OF_CONDUCT.md).

## Security 🧯

See [SECURITY](SECURITY.md).

## Credits 🙏

* [Philippe Gamache](https://ph-il.ca), maintainer, for [Parler Haut, Interagir Librement](https://ph-il.ca)
* [COil](https://github.com/COil), author of [strangebuzz/MicroSymfony](https://github.com/strangebuzz/MicroSymfony),
  and [its contributors](https://github.com/strangebuzz/MicroSymfony/graphs/contributors)
* [All the contributors of WebSkeleton](https://github.com/parler-haut-interagir-librement/WebSkeleton/graphs/contributors)

## License ⚖️

The MIT License, with the attribution to the original repository it asks for. See the [license file](LICENSE).

## Built with WebSkeleton 🛠️

* IRL Assemble Online
* [La Page à Melkor](https://lapageamelkor.org)
* [Parler Haut, Interagir Librement](https://ph-il.ca)
* RPG Assemble Online
* [TiltTek](https://tilttek.com)
* [TTRPG Maker](https://ttrpgmaker.com)
