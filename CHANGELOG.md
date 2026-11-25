# Changelog

All notable changes to this template are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html); its version follows the version of Symfony. WebSkeleton is
built on [WebSymfony](https://github.com/parler-haut-interagir-librement/WebSymfony) and
[MicroSymfony](https://github.com/parler-haut-interagir-librement/MicroSymfony), which follows
[strangebuzz/MicroSymfony](https://github.com/strangebuzz/MicroSymfony).

## [Unreleased]

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
  among them `castor secret`, which writes a random `APP_SECRET` to `.env.local`.
- The documented steps that sign members in with `phil/identity-client-bundle`, and add more recipes of the PHIL UX kit.
