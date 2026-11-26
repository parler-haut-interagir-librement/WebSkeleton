# AGENTS.md - WebSkeleton development guidelines

Guidelines for coding assistants working on this repository, the WebSkeleton site template of PHIL
(`phil/webskeleton`), built on WebSymfony and MicroSymfony, which follows
[strangebuzz/MicroSymfony](https://github.com/strangebuzz/MicroSymfony).

## Project overview

A Symfony 8.2 site template on PHP 8.5 or later, with Doctrine ORM on SQLite, Twig and Twig components, AssetMapper,
Stimulus, Turbo, phil-ux and the recipes of the PHIL UX kit, Symfony Security for the member accounts, EasyAdmin 5 for
the administration, plus the PHIL bundles a site needs: `phil/ddd-bundle`, `phil/browser-security-bundle`,
`phil/legal-notices-bundle`, `phil/accessibility-statement-bundle`, `phil/contact-form-bundle`,
`phil/spam-protection-bundle`, `phil/sitemap-bundle`, `phil/well-known-bundle`, `phil/structured-data-bundle`,
`phil/privacy-bundle`, `phil/error-tracking-bundle`, `phil/menu-bundle`, `phil/doctrine-behaviours-bundle` and
`phil/ux-easyadmin-bundle`. French and English, every page under `/fr` or `/en`, French by default.

`src/` is organised by bounded context, `src/<Context>/{Domain,Application,Infrastructure}`, with `src/Shared` for
what every context uses; Deptrac checks the layers inside each context and that a context uses only itself and
`src/Shared` (contexts listed in `$contexts` of `phil-dev.php`):

- `Domain`: value objects and ports, plain PHP, never services; they throw the `InvalidValue` of `phil/ddd-bundle`.
- `Application`: commands, queries and their handlers, on the buses of `phil/ddd-bundle`.
- `Infrastructure`: controllers (`Http/Controller` under the language prefix, `Http/Api` under `/api`, `Http/Root`
  at the root, found in every context by `config/routes.yaml`), adapters of the ports, Doctrine entities (one mapping
  per context in `config/packages/doctrine.yaml`).
- `src/Shared/Infrastructure`: the controllers of the site pages, the dashboard of the administration, the menu, the
  sitemap provider, Twig; `src/Account`: the member accounts, their pages, their CRUD controller and their module for
  `phil/privacy-bundle`; `src/Text`: the example context, a slug through the three layers.
- `src/Shared` never uses a context: it names their pages by their route names (`account_login`, `admin_user_index`).
- `fixtures/` holds the Foundry factories and stories of the dev and test environments, outside `src/`.

## Tasks

Every task runs through [Castor](https://castor.jolicode.com) (`castor.php`); `composer.json` holds no task script.

```bash
castor start                     # APP_SECRET, SQLite and migrations, then the Symfony binary's web server
castor load-fixtures             # SQLite reset and migrated, with the accounts admin and member
castor up                        # FrankenPHP in worker mode and PostgreSQL with Docker Compose
castor test                      # every PHPUnit suite
castor test:unit                 # one suite: unit, integration, api, functional, e2e
castor test --filter=testSlugify # one test
castor coverage                  # the coverage report and its threshold
castor lint                      # every lint and the static analysis
castor stan                      # PHPStan only
castor fix                       # every fixer
castor ci                        # coverage, then every lint
```

## Code style

1. `declare(strict_types=1);` in every PHP file.
2. Classes are `final` unless they are made to be extended; services take `readonly` promoted properties.
3. Every parameter, return and property is typed natively; a PHPDoc type only refines a native one.
4. Controllers follow the action pattern: one `*Action` class per route, with `__invoke()`.
5. Dates are never compared with `==` nor `===`: compare their timestamps or use `<` and `>`.
6. JavaScript goes in Stimulus controllers, loaded by AssetMapper: no Node.js build, no Webpack Encore.
7. Configuration files are YAML, under `config/`; only `config/bundles.php` and `config/preload.php` are PHP.
8. A port of the domain is bound to its adapter in `config/services.yaml`; messages carry identifiers only.
9. Every text of a page is a translation key, in `translations/messages.fr.yaml` and `messages.en.yaml`.

## Tests

One directory per kind of test under `tests/`, mirroring `src/`: `Unit` (no kernel), `Integration` (the kernel),
`Api` and `Functional` (requests through the kernel), `E2E` (a browser, once Panther is installed). Every test class
names what it covers with `#[CoversClass]` or `#[CoversNothing]`; PHPUnit fails on a deprecation, a notice or a
warning. A test that touches the database uses the `Factories` and `ResetDatabase` traits of Foundry, which migrate a
SQLite file of its own, and the factories of `fixtures/`.

## Quality gates

Before a change is complete, `castor ci` passes: the tests at the coverage threshold, PHP-CS-Fixer, Twig-CS-Fixer,
Biome, PHPStan at the maximum level with the rules of `phil/dev-tools`, Rector, Arkitect, Deptrac, the dependency
analysis, `composer audit`, composer-unused and composer-normalize. An exception to a gate goes in `phil-dev.php`, with
its reason.

## Security

- Never commit a secret: `.env` holds defaults for development only.
- Validate every input, with a form or constraints.
- The sign-out and every action that changes something are POST requests with a CSRF token; impersonation stays off.
- A module that keeps personal data gives `phil/privacy-bundle` a `PersonalDataExporter` and a `PersonalDataEraser`,
  and knows the member by the UUID of their account, never by their email address.
- `castor lint:composer` runs `composer audit`; Composer refuses a version with a known advisory.
