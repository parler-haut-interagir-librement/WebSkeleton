# Accessibility

WebSkeleton checks the accessibility of its own pages on every run of the tests, and gives a site what it needs to
check what its authors publish. Both rest on
[`phil/accessibility-check`](https://github.com/parler-haut-interagir-librement/accessibility-check), which reads HTML
and tests it against WCAG 2.2: text alternatives, the order of the headings, the names of links and buttons, the
contrast of the colours a content gives, the languages, the header cells of tables. A check stops what a machine can
see; it does not replace a test with people and assistive technologies, which the accessibility statement of the site
reports.

## The pages of the site

[AccessibilityTest](../tests/Functional/AccessibilityTest.php), in the functional suite, requests every page the route
smoke test requests — each GET route without a parameter, and each route whose only parameter is the language, once in
French and once in English (`SiteRoutes`, on `Phil\DevTools\Testing\SmokeRoutes`) — as a visitor, then as a signed-in
administrator, and runs the checker on the HTML of each successful page:

- an **error** fails the test, with the rule, its WCAG criterion, the place in the page and the message;
- a **warning** is written out after the page, and the test passes.

A redirect, a page that answers 404 — a legal document not published yet —, XML and JSON have nothing to check. A
new page is checked as soon as its route exists.

### Leaving out a route or a rule

Two constants of the test, each entry with its reason in a comment:

```php
// The prefixes of the route names left out of this test.
private const array EXCLUDED_ROUTES = [
    // The pages of EasyAdmin, /admin and below: its layout names the user menu and the settings menu by an icon
    // alone (link-name, button-name). The site's own pages are checked.
    'admin',
];

// The identifiers of the rules turned off for every page.
private const array DISABLED_RULES = [
    // 'svg-name',
];
```

A route is left out by the beginning of its name: `admin` leaves out `admin`, `admin_user_index` and every other
route of the dashboard. The identifiers of the rules are those of the
[catalogue of the rules](https://github.com/parler-haut-interagir-librement/accessibility-check/blob/main/doc/rules.md).
A route left out of every test, the route smoke test included, goes to `SiteRoutes::EXCLUDED` instead.

Leave a page out only for markup the site does not write, and fix its own templates instead: the pages of EasyAdmin
are left out for the menus of EasyAdmin's layout alone.

## What the authors publish

The pages the template ships are checked by its tests; a content written later, by an author in a form, is checked
when it is saved or published. `phil/accessibility-check` gives two ways, which the template does not wire: a site
that publishes content chooses one of them, or both.

The library is a development requirement of the template, for its test. A site that checks its content in production
moves it to the requirements:

```sh
composer require phil/accessibility-check:^0.1.2@alpha
```

### The services

The checker, its policy and its rules are services, as the
[installation guide of the library](https://github.com/parler-haut-interagir-librement/accessibility-check/blob/main/doc/installation.md#in-a-symfony-application)
shows. In `config/services.yaml`, with one checker per site:

```yaml
services:
    _instanceof:
        Phil\AccessibilityCheck\Contract\Rule:
            tags: ['phil_accessibility_check.rule']

    Phil\AccessibilityCheck\Rule\:
        resource: '%kernel.project_dir%/vendor/phil/accessibility-check/src/Rule/*'
        exclude:
            - '%kernel.project_dir%/vendor/phil/accessibility-check/src/Rule/{DefaultRules,FileName,FragmentRule,GenericLinkText,Heading}.php'

    app.accessibility_policy:
        class: Phil\AccessibilityCheck\Policy
        # The page shows the title of the content above it: the content's headings start at h2.
        arguments: { $headings: !php/enum Phil\AccessibilityCheck\HeadingPolicy::None }

    Phil\AccessibilityCheck\AccessibilityChecker:
        arguments:
            $policy: '@app.accessibility_policy'
            $rules: !tagged_iterator phil_accessibility_check.rule
```

### When it is saved: the validator constraint

`AccessibleContent` is a constraint of the Validator component: a form refuses the content while the checker finds an
error in it, and each finding is a violation the form shows next to the field.

```yaml
services:
    Phil\AccessibilityCheck\Validator\AccessibleContentValidator:
        arguments: ['@Phil\AccessibilityCheck\AccessibilityChecker', '%kernel.default_locale%']
        tags: ['validator.constraint_validator']
```

```php
namespace App\Catalog\Application;

use Phil\AccessibilityCheck\Validator\AccessibleContent;

final readonly class PublishArticleCommand
{
    public function __construct(
        public string $articleId,
        #[AccessibleContent]
        public string $body,
    ) {
    }
}
```

The constraint takes `minSeverity` (`Severity::Error` by default; `Severity::Warning` refuses the warnings too),
`language`, the language of a content that does not say its own, and `locale`, the language of the messages. Without
the service above, the validator checks with the default policy and writes in English. The
[validator guide](https://github.com/parler-haut-interagir-librement/accessibility-check/blob/main/doc/validator.md)
lists the codes and parameters of the violations.

### When it is published: the workflow guard

A site whose contents go through a workflow — draft, review, published — holds back the publication instead:
`PublicationGuard` listens to the guard event of the transition and blocks it while the content has an error. It needs
the Workflow component:

```sh
composer require symfony/workflow:8.2.*
```

The site says where the content of a subject is, with a `SubjectContent` of its infrastructure:

```php
namespace App\Catalog\Infrastructure\Accessibility;

use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Article;
use Phil\AccessibilityCheck\Contract\SubjectContent;
use Phil\Ujo\Document;

final class ArticleBody implements SubjectContent
{
    public function contentOf(object $subject): string|Document|null
    {
        return $subject instanceof Article ? $subject->body() : null;
    }
}
```

```yaml
services:
    Phil\AccessibilityCheck\Workflow\PublicationGuard:
        arguments:
            $checker: '@Phil\AccessibilityCheck\AccessibilityChecker'
            $content: '@App\Catalog\Infrastructure\Accessibility\ArticleBody'
            $blockAt: !php/enum Phil\AccessibilityCheck\Severity::Error
            $locale: '%kernel.default_locale%'
        tags:
            - { name: kernel.event_listener, event: workflow.article.guard.publish }
```

A blocked transition carries a `TransitionBlocker` of code `PublicationGuard::BLOCKED`, whose parameters hold the
whole report, for the screen that tells the author what to fix; a content the checker refuses — too large, not UTF-8
— blocks with `PublicationGuard::REFUSED`. The
[guide of the guard](https://github.com/parler-haut-interagir-librement/accessibility-check/blob/main/doc/publication-guard.md)
shows how to read them.

### From the console

With `Phil\AccessibilityCheck\Console\CheckCommand` registered as a service tagged `console.command`,
`bin/console phil:accessibility:check <file>` checks a file or a fragment of HTML, for a content prepared outside the
site; it never fetches an address.
