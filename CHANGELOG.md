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
- Domain, Application and Infrastructure layers with `phil/ddd-bundle`: a command bus in a Doctrine transaction, a
  query bus, the example of a slug through the three layers, the layers checked by Deptrac.
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
- The PHIL identity of WebSymfony: logo, copyright of Parler Haut, Interagir Librement, credit to Strangebuzz, a KnpMenu
  main menu, cross-origin rules on `/api/` by NelmioCorsBundle, Sentry in production.
- `compose.yaml` with FrankenPHP in worker mode and PostgreSQL, MySQL documented as the other choice, the Symfony binary
  with SQLite as the lighter one.
- The five test suites with the code coverage at 100 %, the quality gates of `phil/dev-tools` and the tasks in Castor,
  among them `castor secret`, which writes a random `APP_SECRET` to `.env.local`.
- The documented steps that add member accounts with `phil/identity-client-bundle`, an administration with EasyAdmin and
  `phil/ux-easyadmin-bundle`, and more recipes of the PHIL UX kit.
