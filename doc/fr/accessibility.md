<!-- phil-dev source-sha256=f7725e611bcb3d9da2a002f47eeadc978f7d6db0f7d04aaea11381823d2c284b -->
# Accessibilité

WebSkeleton vérifie l'accessibilité de ses propres pages à chaque exécution des tests, et donne à un site ce qu'il lui faut
pour vérifier ce que publient ses auteurs. Les deux reposent sur
[`phil/accessibility-check`](https://github.com/parler-haut-interagir-librement/accessibility-check), qui lit du HTML et
le teste contre WCAG 2.2 : les équivalents textuels, l'ordre des titres, les noms des liens et des boutons, le contraste
des couleurs que donne un contenu, les langues, les cellules d'en-tête des tableaux. Une vérification arrête ce qu'une
machine peut voir ; elle ne remplace pas un test avec des personnes et des technologies d'assistance, que rapporte la
déclaration d'accessibilité du site.

## Les pages du site

[AccessibilityTest](../../tests/Functional/AccessibilityTest.php), dans la suite fonctionnelle, requiert chaque page que
requiert le test de fumée des routes — chaque route GET sans paramètre, et chaque route dont le seul paramètre est la
langue, une fois en français et une fois en anglais (`SiteRoutes`, sur `Phil\DevTools\Testing\SmokeRoutes`) — comme
visiteur, puis comme administrateur connecté, et lance le vérificateur sur le HTML de chaque page réussie :

- une **erreur** fait échouer le test, avec la règle, son critère WCAG, l'endroit dans la page et le message ;
- un **avertissement** est affiché après la page, et le test passe.

Une redirection, une page qui répond 404 — un document légal pas encore publié —, du XML et du JSON n'ont rien à
vérifier. Une nouvelle page est vérifiée dès que sa route existe.

### Exclure une route ou une règle

Deux constantes du test, chaque entrée avec sa raison dans un commentaire :

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

Une route s'exclut par le début de son nom : `admin` exclut `admin`, `admin_user_index` et toutes les autres routes du
tableau de bord. Les identifiants des règles sont ceux du
[catalogue des règles](https://github.com/parler-haut-interagir-librement/accessibility-check/blob/main/doc/rules.md).
Une route exclue de tous les tests, le test de fumée des routes compris, va plutôt dans `SiteRoutes::EXCLUDED`.

N'excluez une page que pour un balisage que le site n'écrit pas, et corrigez plutôt ses propres gabarits : les pages
d'EasyAdmin ne sont exclues que pour les menus de la mise en page d'EasyAdmin.

## Ce que publient les auteurs

Les pages que livre le gabarit sont vérifiées par ses tests ; un contenu écrit plus tard, par un auteur dans un
formulaire, est vérifié quand il est enregistré ou publié. `phil/accessibility-check` offre deux façons, que le gabarit
ne branche pas : un site qui publie du contenu en choisit une, ou les deux.

La bibliothèque est un prérequis de développement du gabarit, pour son test. Un site qui vérifie son contenu en
production la passe dans les prérequis :

```sh
composer require phil/accessibility-check:^0.1.2@alpha
```

### Les services

Le vérificateur, sa politique et ses règles sont des services, comme le montre le
[guide d'installation de la bibliothèque](https://github.com/parler-haut-interagir-librement/accessibility-check/blob/main/doc/installation.md#in-a-symfony-application).
Dans `config/services.yaml`, avec un vérificateur par site :

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

### À l'enregistrement : la contrainte du validateur

`AccessibleContent` est une contrainte du composant Validator : un formulaire refuse le contenu tant que le vérificateur
y trouve une erreur, et chaque constat est une violation que le formulaire affiche à côté du champ.

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

La contrainte accepte `minSeverity` (`Severity::Error` par défaut ; `Severity::Warning` refuse aussi les
avertissements), `language`, la langue d'un contenu qui ne dit pas la sienne, et `locale`, la langue des messages. Sans le
service ci-dessus, le validateur vérifie avec la politique par défaut et écrit en anglais. Le
[guide du validateur](https://github.com/parler-haut-interagir-librement/accessibility-check/blob/main/doc/validator.md)
liste les codes et les paramètres des violations.

### À la publication : la garde du workflow

Un site dont les contenus passent par un workflow — brouillon, révision, publié — retient plutôt la publication :
`PublicationGuard` écoute l'événement de garde de la transition et la bloque tant que le contenu a une erreur. Il lui
faut le composant Workflow :

```sh
composer require symfony/workflow:8.2.*
```

Le site dit où se trouve le contenu d'un sujet, avec un `SubjectContent` de son infrastructure :

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

Une transition bloquée porte un `TransitionBlocker` de code `PublicationGuard::BLOCKED`, dont les paramètres contiennent
tout le rapport, pour l'écran qui dit à l'auteur quoi corriger ; un contenu que le vérificateur refuse — trop gros, pas en
UTF-8 — bloque avec `PublicationGuard::REFUSED`. Le
[guide de la garde](https://github.com/parler-haut-interagir-librement/accessibility-check/blob/main/doc/publication-guard.md)
montre comment les lire.

### Depuis la console

Avec `Phil\AccessibilityCheck\Console\CheckCommand` enregistré comme un service étiqueté `console.command`,
`bin/console phil:accessibility:check <file>` vérifie un fichier ou un fragment de HTML, pour un contenu préparé hors
du site ; il ne va jamais chercher une adresse.
