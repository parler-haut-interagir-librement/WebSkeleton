# Changelog

All notable changes to this template are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html); its version follows the version of Symfony. WebSkeleton is
built on [WebSymfony](https://github.com/parler-haut-interagir-librement/WebSymfony) and
[MicroSymfony](https://github.com/parler-haut-interagir-librement/MicroSymfony), which follows
[strangebuzz/MicroSymfony](https://github.com/strangebuzz/MicroSymfony).

## Unreleased

The next version is 8.2.0-RC2.

### Added

- `doc/tailwind.md`: Tailwind CSS instead of PHIL UX CSS, with the Shadcn kit of the Symfony UX Toolkit,
  `phil/ux-kit-shadcn` for the PHIL recipes under the same names and props, and `symfonycasts/tailwind-bundle` on
  AssetMapper — what to remove, what to add, the commands and what changes in the templates. PHIL UX stays the default.
- An accessibility test in the functional suite: `phil/accessibility-check` on the HTML of every page of the route smoke
  test, in French and in English, as a visitor and as an administrator; an error fails, a warning is written out. A route
  or a rule is left out in `EXCLUDED_ROUTES` or `DISABLED_RULES`, with its reason; the pages of EasyAdmin are left out
  for the icon-only menus of its layout. `phil/accessibility-check` is a direct development requirement.
- `doc/accessibility.md`: the accessibility test, and how a site checks what its authors publish with the validator
  constraint `AccessibleContent` or the workflow guard `PublicationGuard` of `phil/accessibility-check`, neither
  wired by default.
- Feature flags by `phil/feature-flags-bundle` 0.1: the flags declared in `config/packages/phil_feature_flags.yaml`,
  their states and the flags created in the administration kept in the database (a migration creates the two tables
  on SQLite, PostgreSQL and MySQL), each change authorized for `ROLE_ADMIN`, with a reason, and written to the
  `feature_flags` log channel before it applies; the screens under `/admin/flags`, linked from the dashboard, in the
  layout of the site. The adapters live in `src/Account/Infrastructure/FeatureFlags`: the member signed in is the
  subject of the targeting rules.
- The flag `text.slug-page` of the example, off by default: the page of the slug of a title, `/fr/slug` and
  `/en/slug`, which works without JavaScript, answers 404 while it is off (`#[IsFeatureEnabled]` on
  `SlugPageAction`), and the home page links to it only while it is on (`feature_enabled()` in Twig); both ways are
  tested.
- Circuit breakers by `phil/circuit-breaker-bundle` 0.2, declared in `config/packages/phil_circuit_breaker.yaml`,
  their states in the cache pool `cache.circuits` shared by the workers (`single_server` in production), each circuit
  forced open by its flag `circuit-open.<key>`. The example circuit `http.example` guards the scoped client
  `example.client`, whose base URI is `EXAMPLE_API_URL`; a test opens it with server errors. In the test environment,
  every HTTP client answers from `tests/Support/MockResponses`: no request leaves the machine. `symfony/http-client`,
  `symfony/clock` and `psr/log` are direct requirements, `symfony/http-client-contracts` a development one.
- `doc/feature-flags-and-circuit-breakers.md`: what is wired, the examples, where a flag is read in the layers (never
  in the Domain), how to add a flag or a circuit, and how to do without the screens.
- Typed identifiers of `phil/ddd-bundle` 0.2 in the context `Account`: `AccountId`, a UUIDv7 of its own class, is the
  identifier of `User`, stored by the DBAL type `AccountIdType` registered as `account_id` in `doctrine.yaml` (the same
  column as before: no migration); `UserRepository::find()` also takes the string of an `AccountId`, as EasyAdmin
  passes it, and finds nothing for any other string; `AccountPersonalData` turns the identifier of the privacy bundle
  back into an `AccountId`.
- `GET /api/accounts/{account}`, for `ROLE_ADMIN`: `ShowAccountAction` receives the placeholder as an `AccountId`
  through the value resolver of `phil/ddd-bundle` (anything else is a 404), asks `ShowAccountQuery` on the query bus,
  answered by the port `AccountViews` and its adapter `DoctrineAccountViews`, and writes `AccountView` with the
  Serializer, whose normaliser from the bundle gives the identifier as its string; no email address, no password.
  `symfony/serializer` is a direct requirement.
- `doc/ddd.md`: the typed identifiers from the column to the controller and the JSON, and the `make:ddd:*` commands of
  `phil/ddd-bundle` with `symfony/maker-bundle` in development, in the layout `src/<Context>/{Domain,Application,Infrastructure}`
  of the template, with the configuration left to write in YAML.

### Changed

- Every requirement of `composer.json`, `require` and `require-dev`, at its latest released version: `^X.Y` of the
  latest minor (`^0.Y.Z@alpha` for the PHIL packages), the Symfony components at `8.2.*`; among them
  `phil/privacy-bundle` 0.4, `phil/spam-protection-bundle` 0.3 and `phil/ux-kit` 0.7.
- `phil/dev-tools` 0.2.1-alpha, taken by a Composer `path` repository to `../dev-tools` until it is on Packagist; the
  `repositories` entry is removed at the release.
- `castor.php` imports the common tasks of `phil/dev-tools`, `vendor/phil/dev-tools/resources/castor/tasks.php`, set by
  its `PHIL_CASTOR` constant, and keeps only the template's own tasks: the servers, Docker Compose, the database, the
  environments, `test:schema`, `lint:doctrine` and `check-requirements`. The names of the tasks stay; `castor lint`
  runs `lint:doctrine` after the common lints, `castor ci` runs `test:schema` after the coverage and the lints, and
  `castor test:suite <name>` runs one suite. `bin/coverage-checker.php` is gone: `castor coverage` checks the
  threshold itself.
- A migration drops the tables of `phil/privacy-bundle` whose features the template leaves off — the requests without
  an account, the consent to tracked emails, the erasure workflow —, which the bundle no longer maps from 0.4.
- PHPUnit writes without colours, so that `castor coverage` reads the summary of its text report;
  `castor test --options=--colors=always` shows them.
- The route smoke test, `RouteSmokeTest`, on `Testing\RouteSmokeTesting` of `phil/dev-tools`, with one set of route
  parameters per language of `app.locales`.
- The titles of the cards of the home page are `h2`, under its `h1`.
- The configuration back in YAML: `config/packages/*.yaml`, those of the PHIL bundles among them, with `when@dev`,
  `when@test` and `when@prod` blocks, `config/routes.yaml`, `config/routes/*.yaml` and `config/services.yaml`; only
  `config/bundles.php` and `config/preload.php` stay PHP. `castor lint:yaml` checks `config/` too.
- `phil/cors-bundle` instead of `nelmio/cors-bundle` for the cross-origin rules of `/api/`, in
  `config/packages/phil_cors.yaml`: the origins that the regular expression `CORS_ALLOW_ORIGIN` matches, `GET`, the
  `Content-Type` header, a preflight kept 3600 seconds, no credentials; the preflight is answered before the router.
  `CorsTest` checks the preflight and the simple request from an allowed origin, and the refusal of a foreign origin,
  of another method and of another header. `phil/cors-bundle` 0.1.0-alpha is taken by a Composer `path` repository to
  `../phil/packages/cors-bundle` until it is on Packagist; the `repositories` entry is removed at the release.
- `phil/ddd-bundle` 0.2.0-alpha, taken by a Composer `path` repository to `../phil/packages/ddd-bundle` until it is on
  Packagist, and required as `0.2.0-alpha as 0.1.99-alpha` until then, since `phil/feature-flags-bundle` 0.1.2-alpha
  asks for `^0.1`; at the release, the `repositories` entry is removed and the requirement becomes `^0.2.0@alpha`.
- `symfony/uid` is no longer a direct requirement: the classes of `src/` use the identifiers of `phil/ddd-bundle`.
- The Doctrine mappings of the contexts declare no `alias`, deprecated by DoctrineBundle 3.4.
- `services.yaml` leaves out the views of the applications, `src/*/Application/**/*View.php`, as it does the commands
  and the queries.

## [8.2.0-RC1] - 2026-11-25

### Added

- Symfony 8.2 from its first release candidate, on PHP 8.5 or later, every other dependency at its latest version;
  installed by `composer create-project phil/webskeleton`, or used as a GitHub template repository.
- French and English: every page under `/fr` or `/en`, French by default, `/` sending a visitor to the language their
  browser prefers; the languages in one list, `app.locales`, one more by configuration; a language switcher and
  `hreflang` alternates in the layout.
- Domain, Application and Infrastructure layers in each bounded context, `src/<Context>/`, with `src/Shared` for what
  every context uses, on `phil/ddd-bundle`: a command bus in a Doctrine transaction, a query bus, the example context
  `Text` of a slug through the three layers; Deptrac checks the layers inside each context and the isolation between
  contexts.
- Member accounts in the context `Account`: sign-up through the command bus with validation, form login with a CSRF
  token and throttled attempts, the account page, sign-out by POST with a CSRF token, every page under `/fr` and
  `/en`; `ROLE_ADMIN` above `ROLE_USER`, the user checked again on each refresh, impersonation off, the session holding a
  crc32c hash of the password hash; the migration, and the fixtures `admin` and `member` loaded by
  `castor load-fixtures`. `phil/identity-client-bundle` is documented as the sign-in by OpenID Connect.
- The administration, EasyAdmin 5 in the theme of PHIL UX by `phil/ux-easyadmin-bundle`, at `/admin` for `ROLE_ADMIN`:
  the accounts listed and shown, and the errors of the site.
- The privacy choices of each member under the language prefix — the export of their data, the deletion of their
  account, the consent — and the redaction of the logs, by `phil/privacy-bundle`, with the account as a module of the
  export and the erasure.
- The errors of the requests, messages and commands kept in the database of the site, by `phil/error-tracking-bundle`.
- The main menu by `phil/menu-bundle`, and the creation and update dates of the accounts by
  `phil/doctrine-behaviours-bundle`.
- phil-ux, the stylesheet of `phil/ux-css`, and the recipes `Badge`, `Button`, `Card`, `Table` and `ThemeSwitcher` of
  the PHIL UX kit, light and dark.
- A Content Security Policy with nonces, the security headers and the violation reports, by
  `phil/browser-security-bundle`.
- The legal notice and the privacy policy in both languages, as drafts, by `phil/legal-notices-bundle`; the
  accessibility statement, unpublished until written, by `phil/accessibility-statement-bundle`.
- A contact form protected by a proof of work, by `phil/contact-form-bundle` and `phil/spam-protection-bundle`, with
  Mailpit in Docker Compose.
- `/sitemap.xml` with every page in every language and its alternates, by `phil/sitemap-bundle`;
  `/.well-known/security.txt`, by `phil/well-known-bundle`; the JSON-LD graph of each page, by
  `phil/structured-data-bundle`.
- The PHIL identity of WebSymfony: logo, copyright of Parler Haut, Interagir Librement, credit to Strangebuzz,
  cross-origin rules on `/api/` by NelmioCorsBundle.
- `compose.yaml` with FrankenPHP in worker mode and PostgreSQL, MySQL documented as the other choice, the Symfony binary
  with SQLite as the lighter one.
- The five test suites with the code coverage at 100 %, the quality gates of `phil/dev-tools` and the tasks in Castor,
  among them `castor secret`, which writes a random `APP_SECRET` to `.env.local`, and `castor test:schema`, which runs
  the migrations on a fresh SQLite database and compares its schema with the Doctrine mapping.
- A GitHub Actions workflow: security, tests on PHP 8.5 and 8.6 with the coverage threshold and the migrations against
  the Doctrine mapping, lints without a database; every action pinned to a commit, the checkout without credentials.
- The security files of `phil/dev-tools` in `.github/`: Dependabot with a cooldown of seven days for the root, each
  tool of `vendor-bin/` and the actions, the OpenSSF Scorecard and zizmor, their actions pinned to a commit.
- The documented steps that sign members in with `phil/identity-client-bundle`, and add more recipes of the PHIL UX kit.
