<!-- phil-dev source-sha256=17d3e9ccb28a53f1a1552f8f3edf12fde6e9cec2ee73d8465c47438e21dd13c8 -->
# WebSkeleton 🦴

[![Latest Version](https://img.shields.io/packagist/v/phil/webskeleton.svg?style=flat-square)](https://packagist.org/packages/phil/webskeleton)
[![PHP Version Require](https://poser.pugx.org/phil/webskeleton/require/php?style=flat-square)](https://packagist.org/packages/phil/webskeleton)
[![Symfony Version](https://img.shields.io/badge/Symfony-8.2-black?logo=symfony)](https://symfony.com/8)
[![PHPStan level](https://img.shields.io/badge/PHPStan-level%20max-success?style=flat-square)](phpstan.neon)
[![Software License](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status (GitHub)](https://img.shields.io/github/actions/workflow/status/parler-haut-interagir-librement/WebSkeleton/symfony.yml?branch=main&style=flat-square)](https://github.com/parler-haut-interagir-librement/WebSkeleton/actions?query=workflow%3ASymfony+branch%3Amain)

## À propos 🖋

« **Parler** *Haut*, **Interagir** *Librement* » **Web***Skeleton* est la base d'un site [Symfony 8.2](https://symfony.com/8) :
en français et en anglais, organisé en conception pilotée par le domaine (Domain, Application, Infrastructure), habillé
par le kit PHIL UX, avec des comptes de membres, une administration EasyAdmin et les bundles PHIL dont un site public a
besoin dès son premier jour — en-têtes de sécurité, mentions légales, politique de confidentialité, choix de
confidentialité des membres, déclaration d'accessibilité, formulaire de contact, suivi des erreurs, plan du site,
`security.txt` et données structurées —, et les mêmes tests et portes de qualité que les autres gabarits de PHIL.

WebSkeleton est bâti sur [WebSymfony](https://github.com/parler-haut-interagir-librement/WebSymfony), lui-même bâti sur
[MicroSymfony](https://github.com/parler-haut-interagir-librement/MicroSymfony), la version PHIL de
[strangebuzz/MicroSymfony](https://github.com/strangebuzz/MicroSymfony), créé et maintenu par
[COil](https://github.com/COil) de [Strangebuzz](https://www.strangebuzz.com). C'est le premier de ces gabarits avec les
outils PHIL : phil-ux à la place de Pico CSS, le kit PHIL UX et les bundles PHIL.

## Table des matières 📖

* [Démo](#démo-)
* [Démarrage rapide](#démarrage-rapide-)
* [Prérequis](#prérequis-)
* [Pile technologique](#pile-technologique-)
* [Ce qu'il ajoute à WebSymfony](#ce-quil-ajoute-à-websymfony-)
* [Configuration](#configuration-)
* [Langues](#langues-)
* [Conception pilotée par le domaine](#conception-pilotée-par-le-domaine-)
* [Comptes et administration](#comptes-et-administration-)
* [Indicateurs de fonctionnalité et disjoncteurs](#indicateurs-de-fonctionnalité-et-disjoncteurs-)
* [Accessibilité](#accessibilité-)
* [Avant la mise en ligne](#avant-la-mise-en-ligne-)
* [Étapes facultatives](#étapes-facultatives-)
* [Tâches](#tâches-)
* [Portes de qualité](#portes-de-qualité-)
* [Ce qu'il ne livre pas](#ce-quil-ne-livre-pas-)
* [Références](#références-)
* [Les autres gabarits « Parler Haut, Interagir Librement »](#les-autres-gabarits--parler-haut-interagir-librement--)
* [Contribuer](#contribuer-)
* [Sécurité](#sécurité-)
* [Crédits](#crédits-)
* [Licence](#licence-)
* [Bâti avec WebSkeleton](#bâti-avec-webskeleton-)

## Démo 🌈

Une démo de WebSkeleton tournera dans le navigateur sur [ph-il.ca](https://ph-il.ca), avec les autres gabarits de PHIL.

## Démarrage rapide ⚡

Créez un nouveau projet à partir de la dernière version :

    composer create-project phil/webskeleton my-site && cd my-site

Le dépôt est aussi un dépôt gabarit de GitHub : le bouton « Use this template » crée un nouveau dépôt à partir de la
branche principale, dont tous les commits passent l'intégration continue.

### Avec le binaire Symfony 🕸️

Il vous faut le [binaire Symfony](https://symfony.com/download#step-1-install-symfony-cli) et
[Castor](https://castor.jolicode.com/getting-started/installation/). La base de données est un fichier SQLite dans
`var/` :

    castor start

La tâche écrit un `APP_SECRET` aléatoire dans `.env.local` s'il n'en contient pas, prépare la base de données et lance
le serveur. Ouvrez [https://127.0.0.1:8000](https://127.0.0.1:8000) (si le port 8000 est libre) : `/` vous envoie vers
`/fr` ou `/en`, selon la langue de votre navigateur. `castor stop` arrête le serveur et efface la base de données.

### Avec FrankenPHP et PostgreSQL 🧟

Il vous faut [Docker](https://www.docker.com/). `compose.yaml` sert l'application avec [FrankenPHP](https://frankenphp.dev/)
en mode worker, qui démarre le noyau une fois et sert requête après requête, et redémarre ses workers quand un fichier
change ; la base de données est PostgreSQL 17, et [Mailpit](https://mailpit.axllent.org/) recueille les messages du
formulaire de contact :

    castor secret && castor up

Ouvrez [https://localhost](https://localhost). `castor down` arrête les services. L'image de `docker/Dockerfile` sert au
développement : un site bâti sur ce gabarit apporte son propre déploiement.

### Avec MySQL 🐬

MySQL est l'autre base de données que le gabarit prend en charge. Dans `compose.yaml`, remplacez le service `database`
par une image `mysql:8.4` et donnez à `DATABASE_URL` :

    mysql://app:app@database:3306/app?serverVersion=8.4&charset=utf8mb4

Avec le binaire Symfony, mettez la même variable dans `.env.local`, avec l'hôte de votre serveur.

## Prérequis ⚙

* [PHP 8.5](https://www.php.net/releases/8.5/fr.php) ou plus récent, avec les extensions de `composer.json`, `intl` et
  `sodium`, et [Castor](https://castor.jolicode.com)
* Le [binaire Symfony](https://symfony.com/download), ou [Docker](https://www.docker.com)
* L'extension [Xdebug](https://xdebug.org/) ou [PCOV](https://github.com/krakjoe/pcov) pour le rapport de couverture
  du code

## Pile technologique 🔗

* [PHP 8.5](https://www.php.net/releases/8.5/fr.php)
* [Symfony 8.2](https://symfony.com/8), depuis sa version candidate, avec [Messenger](https://symfony.com/doc/current/messenger.html)
  pour les commandes et les requêtes
* [Doctrine ORM 3 (DBAL 4)](https://www.doctrine-project.org), sur SQLite, PostgreSQL ou MySQL
* [Symfony Security](https://symfony.com/doc/current/security.html) pour les comptes de membres, et
  [EasyAdmin 5](https://symfony.com/bundles/EasyAdminBundle/current/index.html) pour l'administration
* [Twig 3](https://twig.symfony.com) et les [composants Twig](https://symfony.com/bundles/ux-twig-component/current/index.html)
* [AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html), [Stimulus 3.2](https://stimulus.hotwired.dev/)
  et [Turbo 8](https://turbo.hotwired.dev/), sans Node.js
* [phil-ux](https://github.com/parler-haut-interagir-librement/ux-css), la feuille de style de PHIL, en clair et en
  sombre, et les recettes du [kit PHIL UX](https://github.com/parler-haut-interagir-librement/ux-kit)
* [PHPUnit 13](https://phpunit.de)
* [FrankenPHP](https://frankenphp.dev/) en mode worker, par Docker Compose

## Ce qu'il ajoute à WebSymfony 🚀

| Fonctionnalité | Où |
|---|---|
| Le français et l'anglais, chaque page sous `/fr` ou `/en`, le français par défaut | [routes.yaml](config/routes.yaml), [services.yaml](config/services.yaml), [translations](translations) |
| Les couches Domain, Application et Infrastructure de chaque contexte délimité, avec les bus de `phil/ddd-bundle` | [src](src), [messenger.yaml](config/packages/messenger.yaml) |
| Les identifiants typés de `phil/ddd-bundle` — un type Doctrine chacun, les arguments de contrôleur, le Serializer — et ses commandes `make:ddd:*` en développement | [AccountId](src/Account/Domain/AccountId.php), [doc](doc/fr/ddd.md) |
| Les comptes de membres : l'inscription, la connexion, la page du compte et la déconnexion, sous `/fr` et `/en`, dans le contexte `Account` | [src/Account](src/Account), [security.yaml](config/packages/security.yaml) |
| L'administration, EasyAdmin au thème de PHIL UX par `phil/ux-easyadmin-bundle`, pour `ROLE_ADMIN` | [DashboardController](src/Shared/Infrastructure/Http/Admin/DashboardController.php), [phil_ux_easy_admin.yaml](config/packages/phil_ux_easy_admin.yaml) |
| Les choix de confidentialité de chaque membre — l'exportation de ses données, la suppression de son compte, le consentement — et le caviardage des journaux, par `phil/privacy-bundle` | [phil_privacy.yaml](config/packages/phil_privacy.yaml), [AccountPersonalData](src/Account/Infrastructure/Privacy/AccountPersonalData.php) |
| Les erreurs du site gardées dans sa propre base et montrées dans l'administration, par `phil/error-tracking-bundle` | [phil_error_tracking.yaml](config/packages/phil_error_tracking.yaml) |
| Des indicateurs de fonctionnalité, éteints jusqu'à ce qu'un administrateur les allume sous `/admin/flags`, par `phil/feature-flags-bundle` | [phil_feature_flags.yaml](config/packages/phil_feature_flags.yaml), [doc](doc/fr/feature-flags-and-circuit-breakers.md) |
| Un disjoncteur autour de chaque tiers que le site appelle, par `phil/circuit-breaker-bundle` | [phil_circuit_breaker.yaml](config/packages/phil_circuit_breaker.yaml), [doc](doc/fr/feature-flags-and-circuit-breakers.md) |
| Le menu principal, par `phil/menu-bundle` | [MainMenuBuilder](src/Shared/Infrastructure/Menu/MainMenuBuilder.php) |
| Les dates de création et de modification des comptes, par `phil/doctrine-behaviours-bundle` | [User](src/Account/Infrastructure/Persistence/Doctrine/Entity/User.php), [phil_doctrine_behaviours.yaml](config/packages/phil_doctrine_behaviours.yaml) |
| phil-ux et les recettes du kit PHIL UX dans `templates/components/` | [base.html.twig](templates/base.html.twig), [components](templates/components) |
| Une politique de sécurité du contenu avec nonces et les en-têtes de sécurité, par `phil/browser-security-bundle` | [phil_browser_security.yaml](config/packages/phil_browser_security.yaml) |
| Les mentions légales et la politique de confidentialité, dans les deux langues, par `phil/legal-notices-bundle` | [phil_legal_notices.yaml](config/packages/phil_legal_notices.yaml), [legal](legal) |
| La déclaration d'accessibilité, par `phil/accessibility-statement-bundle` | [phil_accessibility_statement.yaml](config/packages/phil_accessibility_statement.yaml) |
| Un formulaire de contact protégé du pourriel, par `phil/contact-form-bundle` et `phil/spam-protection-bundle` | [phil_contact_form.yaml](config/packages/phil_contact_form.yaml) |
| `/sitemap.xml`, chaque page dans chaque langue avec ses alternatives, par `phil/sitemap-bundle` | [LocalizedPagesUrlProvider](src/Shared/Infrastructure/Sitemap/LocalizedPagesUrlProvider.php) |
| Les règles d'origine croisée des points d'accès JSON sous `/api/`, pour les origines que `CORS_ALLOW_ORIGIN` reconnaît, par `phil/cors-bundle` | [phil_cors.yaml](config/packages/phil_cors.yaml), [CorsTest](tests/Functional/CorsTest.php) |
| `/.well-known/security.txt`, par `phil/well-known-bundle` | [phil_well_known.yaml](config/packages/phil_well_known.yaml) |
| Le graphe JSON-LD de chaque page, par `phil/structured-data-bundle` | [phil_structured_data.yaml](config/packages/phil_structured_data.yaml) |
| Chaque page vérifiée pour l'accessibilité, dans les deux langues, par `phil/accessibility-check`, et chaque route par le test de fumée de `phil/dev-tools` | [AccessibilityTest](tests/Functional/AccessibilityTest.php), [RouteSmokeTest](tests/Functional/RouteSmokeTest.php) |

De WebSymfony restent l'identité de PHIL (logo, droits de Parler Haut, Interagir Librement, thèmes clair et sombre), les
comptes de membres et leur administration, les règles d'origine croisée sur `/api/`, les cinq suites de tests, Castor,
les portes de qualité et Docker Compose. Les bundles PHIL prennent la place de KnpMenu, de Sentry, de NelmioCorsBundle
et des extensions Doctrine de Gedmo.

Chaque paquet PHIL vient de Packagist.
`phil/monitoring-bundle` est requis sans être enregistré : la configuration de `phil/error-tracking-bundle`
0.1.0-alpha lit une classe qui implémente une de ses interfaces.

## Configuration 🗂️

La configuration de Symfony et des bundles est en YAML, sous `config/` : `config/packages/*.yaml`, avec des blocs
`when@dev`, `when@test` et `when@prod` pour ce qui change selon l'environnement, `config/routes.yaml`,
`config/routes/*.yaml` et `config/services.yaml`. Les bundles de PHIL sont configurés dans le même format, tel que leurs
recettes Flex l'écrivent, et une recette ajoute ses propres fichiers à côté de ceux-là. Seuls `config/bundles.php` et
`config/preload.php` restent en PHP. `castor lint:yaml` vérifie chaque fichier de `config/`, et `castor lint:container`
vérifie le conteneur qu'ils construisent.

```yaml
# config/packages/phil_sitemap.yaml
phil_sitemap:
    default_section: main

when@dev: &dev
    phil_sitemap:
        allow_localhost: true

when@test: *dev
```

## Langues 🌍

Chaque page vit sous le préfixe de sa langue : `/fr`, `/en`. Le français est la langue par défaut : `/` envoie le
visiteur vers la langue que son navigateur préfère parmi celles du site, le français s'il n'en préfère aucune. La mise
en page relie chaque page à ses autres langues, et le plan du site les donne comme alternatives.

Les langues sont la liste `app.locales` dans [config/services.yaml](config/services.yaml), la langue par défaut en
premier, et `app.locale_requirement` à côté, les mêmes codes joints par `|` pour le préfixe des routes. Pour ajouter
l'espagnol :

1. ajoutez `es` à `app.locales` et à `app.locale_requirement`, `fr|en|es` ;
2. copiez `translations/messages.en.yaml` en `translations/messages.es.yaml` et traduisez-le ;
3. donnez aux documents légaux leur texte `es` dans `legal/` et dans
   [phil_legal_notices.yaml](config/packages/phil_legal_notices.yaml).

Les pages de `/es` existent alors, avec les routes des bundles PHIL sous le même préfixe.

## Conception pilotée par le domaine 🧭

`src/` est organisé par contexte délimité : chaque contexte a ses trois couches, `src/<Contexte>/Domain`,
`src/<Contexte>/Application` et `src/<Contexte>/Infrastructure`, et `src/Shared` contient ce que tous les contextes
utilisent. Deptrac vérifie les deux sortes de règles (`castor lint:architecture`, règles dans `phil-dev.php`).

Dans un contexte, les couches :

| Couche | Contient | Peut utiliser |
|---|---|---|
| `Domain` | Objets-valeurs, entités du modèle, ports (interfaces) : du PHP simple, jamais des services | PHP, les composants Symfony, le domaine de `phil/ddd-bundle` |
| `Application` | Commandes, requêtes et leurs gestionnaires | Le domaine, les bus de `phil/ddd-bundle` |
| `Infrastructure` | Contrôleurs, adaptateurs des ports, entités Doctrine | Tout ce qu'elle intègre |

Entre les contextes, l'isolement : un contexte peut utiliser la même couche de `src/Shared` et les couches en dessous,
jamais un autre contexte ; `src/Shared` n'utilise aucun contexte, et nomme les pages des autres par leurs routes. Ici,
`src/Shared/Infrastructure` contient les contrôleurs des pages du site, le tableau de bord de l'administration, le
menu, le fournisseur du plan du site, Twig et les entités Doctrine que tous les contextes partagent.

```text
src/
├── Kernel.php
├── Account/
│   ├── Domain/                 # AccountId, Role, Accounts (the port)
│   ├── Application/            # SignUpCommand, ShowAccountQuery, their handlers, AccountView, AccountViews (a port)
│   └── Infrastructure/
│       ├── Form/               # the sign-up form
│       ├── Http/Admin/         # UserCrudController
│       ├── Http/Api/           # /api/accounts/{account}
│       ├── Http/Controller/    # login, signup, account, logout
│       ├── Persistence/Doctrine/  # DoctrineAccounts, DoctrineAccountViews, UserRepository, Entity/User,
│       │                          # Type/AccountIdType
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

Le contexte d'exemple, `Text`, traverse les trois couches : [Slug](src/Text/Domain/Slug.php) est un objet-valeur qui
se valide lui-même et lance l'`InvalidValue` de `phil/ddd-bundle` ; [TitleSlugger](src/Text/Domain/TitleSlugger.php)
est son port ; [SlugifyTitleQuery](src/Text/Application/SlugifyTitleQuery.php) et son gestionnaire répondent sur le
bus de requêtes ; [AsciiTitleSlugger](src/Text/Infrastructure/AsciiTitleSlugger.php) est l'adaptateur sur le slugger
de Symfony, lié au port dans [services.yaml](config/services.yaml) ;
[SlugifyAction](src/Text/Infrastructure/Http/Api/SlugifyAction.php) interroge le bus et répond en JSON, ce que la page
d'accueil appelle par un contrôleur Stimulus.

Le bus de commandes s'exécute dans une transaction Doctrine, le bus de requêtes non ; les deux sont synchrones tant
qu'aucun message n'est routé vers un transport dans [messenger.yaml](config/packages/messenger.yaml). Les messages
portent des identifiants, jamais des objets entiers.

Le contexte `Account` montre les identifiants de `phil/ddd-bundle` : [AccountId](src/Account/Domain/AccountId.php) est un
UUIDv7 de sa propre classe, enregistré par son type DBAL [AccountIdType](src/Account/Infrastructure/Persistence/Doctrine/Type/AccountIdType.php),
déclaré dans [doctrine.yaml](config/packages/doctrine.yaml) ; [ShowAccountAction](src/Account/Infrastructure/Http/Api/ShowAccountAction.php),
pour les administrateurs, reçoit le paramètre de `/api/accounts/{account}` sous forme d'`AccountId` — toute autre valeur
est un 404 — et le Serializer écrit l'identifiant de la réponse sous forme de chaîne. Les commandes `make:ddd:*` du
bundle, dans l'environnement de développement, écrivent un contexte et ses classes selon cette disposition. Les deux
sont dans [doc/fr/ddd.md](doc/fr/ddd.md).

### Ajouter un contexte

Pour un contexte nommé `Catalog`, `bin/console make:ddd:context Catalog` écrit les dossiers et un README, et les autres
commandes `make:ddd:*` écrivent ses identifiants, commandes, requêtes et événements ([doc/fr/ddd.md](doc/fr/ddd.md)) ;
ensuite :

1. **Les dossiers** : `src/Catalog/Domain`, `src/Catalog/Application` et `src/Catalog/Infrastructure`, avec les
   espaces de noms `App\Catalog\Domain`, `App\Catalog\Application` et `App\Catalog\Infrastructure` ; ses tests les
   reprennent sous `tests/Unit/Catalog/` et `tests/Integration/Catalog/`.
2. **Les services** : rien à ajouter pour les dossiers, puisque [services.yaml](config/services.yaml) charge `src/` et
   écarte chaque `src/*/Domain/` ainsi que les commandes et requêtes de chaque `src/*/Application/`. Chaque port du
   domaine y reçoit son alias vers son adaptateur, à côté de `TitleSlugger`.
3. **Les routes** : rien à ajouter non plus : [routes.yaml](config/routes.yaml) trouve les contrôleurs de chaque contexte
   dans `Infrastructure/Http/Controller` (sous le préfixe de langue), `Infrastructure/Http/Api` (sous `/api`) et
   `Infrastructure/Http/Root`.
4. **Le mapping Doctrine** : un mapping `Catalog` dans [doctrine.yaml](config/packages/doctrine.yaml), sur
   `src/Catalog/Infrastructure/Persistence/Doctrine/Entity`, comme celui de `Text`, et le type DBAL de chacun de ses
   identifiants sous `doctrine.dbal.types`, comme `account_id`.
5. **Deptrac** : `'Catalog'` ajouté à `$contexts` dans `phil-dev.php`, ce qui lui donne ses trois couches et les deux
   sortes de règles.

## Comptes et administration 🔐

Le contexte `Account` contient les comptes de membres. [User](src/Account/Infrastructure/Persistence/Doctrine/Entity/User.php)
est une entité Doctrine de son infrastructure, identifiée par un [AccountId](src/Account/Domain/AccountId.php), qui est
aussi ce par quoi `phil/privacy-bundle` connaît le membre, jamais par son adresse courriel. L'inscription passe par le bus de commandes : `SignUpCommand`, son
gestionnaire, et le port `Accounts` du domaine, dont l'adaptateur hache le mot de passe et garde le compte.

| Page | Chemin | Ce qu'elle fait |
|---|---|---|
| Inscription | `/fr/signup`, `/en/signup` | Un nom d'utilisateur, une adresse courriel et un mot de passe de 12 caractères ou plus, tapé deux fois ; le nom et l'adresse sont uniques. Un formulaire valide crée le compte et le connecte. |
| Connexion | `/fr/login`, `/en/login` | La connexion par formulaire de Symfony, avec un jeton CSRF ; cinq essais ratés par minute sont permis par nom d'utilisateur et adresse. |
| Compte | `/fr/account`, `/en/account` | Le compte du membre, un lien vers ses choix de confidentialité et le bouton qui le déconnecte. |
| Déconnexion | `/fr/logout`, `/en/logout` | Par POST avec un jeton CSRF seulement : un lien ou une image ne déconnecte jamais personne. |
| Choix de confidentialité | `/fr/privacy-choices`, `/en/privacy-choices` | L'exportation des données du membre, la suppression de son compte après trente jours, le consentement ; la suppression redemande le mot de passe quand la session n'est pas récente. |
| Administration | `/admin` | Pour `ROLE_ADMIN` seulement, qui comprend `ROLE_USER` : les comptes, listés et montrés, jamais créés ni modifiés là, les erreurs du site, et les indicateurs de fonctionnalité sous `/admin/flags`. |
| Compte en JSON | `/api/accounts/{account}` | Pour `ROLE_ADMIN` seulement : l'identifiant, le nom d'utilisateur, les rôles et la date de création d'un compte, jamais son adresse courriel ni son mot de passe ; tout ce qui n'est pas l'identifiant d'un compte est un 404. |

Le pare-feu vérifie de nouveau le compte chaque fois qu'il est rechargé de la session (`user_checker_on_refresh: true`) :
un compte modifié ou désactivé est déconnecté à sa requête suivante. La session garde un hachage crc32c du hachage du
mot de passe, jamais le hachage lui-même ; un mot de passe changé déconnecte les autres sessions. L'usurpation
d'identité reste désactivée ; si un site l'active, seulement par POST avec un jeton CSRF, jamais par un paramètre
d'adresse.

`castor load-fixtures` remet la base SQLite à zéro et charge deux comptes nommés et dix autres membres, pour le
développement seulement :

| Nom d'utilisateur | Mot de passe | Rôles |
|---|---|---|
| `admin` | `admin-password` | `ROLE_ADMIN` |
| `member` | `member-password` | `ROLE_USER` |

Un contexte ajoute ses propres pages à l'administration par un contrôleur CRUD dans son dossier `Infrastructure/Http/Admin/`,
et un lien dans le menu du [DashboardController](src/Shared/Infrastructure/Http/Admin/DashboardController.php), par le
nom qu'EasyAdmin donne à ses routes. Ses renseignements personnels prennent part à l'exportation et à l'effacement d'un
membre par un `PersonalDataExporter` et un `PersonalDataEraser`, comme le fait `AccountPersonalData`.

Pour connecter les membres par un fournisseur d'identité, par OpenID Connect, au lieu des mots de passe, remplacez la
connexion par formulaire par [`phil/identity-client-bundle`](https://github.com/parler-haut-interagir-librement/identity-client-bundle),
comme le montre son guide d'installation : le pare-feu garde `user_checker_on_refresh: true`, et l'inscription, les
mots de passe et leurs données de développement s'en vont.

## Indicateurs de fonctionnalité et disjoncteurs 🎚️

Une nouvelle fonctionnalité sort derrière un indicateur de [`phil/feature-flags-bundle`](https://github.com/parler-haut-interagir-librement/feature-flags-bundle),
déclaré éteint dans [phil_feature_flags.yaml](config/packages/phil_feature_flags.yaml) et allumé par un administrateur
sous `/admin/flags`, avec une raison écrite dans le canal de journal `feature_flags` ; les états vivent dans la base de
données. Un tiers qui échoue n'est plus appelé pendant un temps, grâce à un circuit de
[`phil/circuit-breaker-bundle`](https://github.com/parler-haut-interagir-librement/circuit-breaker-bundle) autour de son
client HTTP, déclaré dans [phil_circuit_breaker.yaml](config/packages/phil_circuit_breaker.yaml) ; chaque circuit a aussi
un indicateur qui le force à s'ouvrir. Les exemples du gabarit sont l'indicateur `text.slug-page`, qui garde la page
`/fr/slug` et son lien sur la page d'accueil, et le circuit `http.example`, autour du client à portée `example.client`.
Ce qui est branché, où un indicateur se lit dans les couches et comment ajouter un indicateur ou un circuit :
[doc/fr/feature-flags-and-circuit-breakers.md](doc/fr/feature-flags-and-circuit-breakers.md).

## Accessibilité ♿

Chaque page du site est vérifiée par [`phil/accessibility-check`](https://github.com/parler-haut-interagir-librement/accessibility-check)
à chaque exécution des tests : [AccessibilityTest](tests/Functional/AccessibilityTest.php) requiert les pages du test de
fumée des routes, en français et en anglais, comme visiteur et comme administrateur, et échoue sur une erreur
d'accessibilité ; un avertissement est affiché et le test passe. Une route ou une règle est exclue dans le test même,
avec sa raison.

Pour vérifier ce que publient les auteurs d'un site — une contrainte du validateur sur un formulaire, une garde qui retient
une publication dans un workflow —, voyez [doc/fr/accessibility.md](doc/fr/accessibility.md). Rien n'en est branché par
défaut.

## Avant la mise en ligne 🚀

Le gabarit ne commence avec rien qui affirme ce que personne n'a vérifié. Avant qu'un site soit public :

1. **Le secret** : mettez `APP_SECRET` dans l'environnement du serveur, ou dans les secrets de Symfony.
2. **Les documents légaux** : remplissez l'identité du site dans
   [phil_legal_notices.yaml](config/packages/phil_legal_notices.yaml), adaptez les textes de `legal/`, puis publiez
   chaque version (une date `published_at`, `state` retiré). D'ici là, `/fr/legal` et `/fr/privacy` répondent 404.
   `bin/console phil:legal-notices:check` signale ce qui manque à un texte.
3. **La déclaration d'accessibilité** : écrivez sa première version une fois le site évalué ; d'ici là, sa page
   répond 404 et le pied de page n'y renvoie pas.
4. **Le formulaire de contact** : remplacez l'expéditeur et le destinataire d'exemple dans
   [phil_contact_form.yaml](config/packages/phil_contact_form.yaml) et réglez `MAILER_DSN`.
5. **`security.txt`** : remplacez le contact dans [phil_well_known.yaml](config/packages/phil_well_known.yaml) et
   repoussez sa date d'expiration, d'au plus un an.
6. **Plusieurs serveurs** : donnez au verrou (`LOCK_DSN`), à `cache.app` et à `cache.circuits` un stockage que tous
   les serveurs partagent, et retirez `single_server` de
   [phil_browser_security.yaml](config/packages/phil_browser_security.yaml), de
   [phil_error_tracking.yaml](config/packages/phil_error_tracking.yaml) et de
   [phil_circuit_breaker.yaml](config/packages/phil_circuit_breaker.yaml).
7. **Les comptes** : ne chargez jamais les données de développement, dont les mots de passe sont publics ; créez le
   premier administrateur en vous inscrivant, puis donnez au compte `ROLE_ADMIN` dans la base.
8. **Les renseignements personnels** : réglez `APP_PUBLIC_ORIGIN` à l'adresse du site, faites les suppressions de
   comptes échues avec `bin/console messenger:consume scheduler_phil_privacy` (avec `symfony/scheduler`) ou `bin/console
   phil:privacy:deletions` par cron, et déclarez dans [config/privacy/cookies.yaml](config/privacy/cookies.yaml) chaque
   témoin que le site ajoute.
9. **Les erreurs** : réglez `APP_RELEASE` à la version qui tourne, et purgez les vieux événements avec
   `bin/console phil:error-tracking:purge` par cron.
10. **Les tiers** : renommez le client d'exemple `example.client` et son circuit `http.example` d'après le tiers que le
    site appelle, ou retirez-les avec `EXAMPLE_API_URL` ; gardez les lignes du canal de journal `feature_flags`, la
    piste d'audit des indicateurs.

## Étapes facultatives 🧩

### Connexion par OpenID Connect

Les comptes se connectent par mot de passe par défaut. Pour les connecter par le fournisseur d'identité de votre choix :

    composer require phil/identity-client-bundle

Remplacez `form_login` dans [security.yaml](config/packages/security.yaml) par l'authentificateur du bundle, gardez
`user_checker_on_refresh: true`, et importez les routes d'`oidc_login` comme le montre le
[guide d'installation du bundle](https://github.com/parler-haut-interagir-librement/identity-client-bundle/blob/main/doc/installation.md).

### Tailwind à la place de PHIL UX CSS

Le gabarit est habillé par phil-ux et le kit PHIL UX. Un site qui préfère Tailwind CSS prend le kit Shadcn du Symfony UX
Toolkit et `phil/ux-kit-shadcn`, les recettes PHIL UX en Tailwind sous les mêmes noms et les mêmes propriétés, avec
`symfonycasts/tailwind-bundle` sur AssetMapper : [doc/fr/tailwind.md](doc/fr/tailwind.md) donne ce qu'il faut retirer,
ce qu'il faut ajouter, les commandes et ce qui change dans les gabarits.

### D'autres recettes du kit PHIL UX

Les recettes de `templates/components/` — `Badge`, `Button`, `Card`, `Table`, `ThemeSwitcher` — sont des copies qui
appartiennent au site. Les autres viennent du kit avec le Symfony UX Toolkit :

    bin/console ux:install accordion --kit=github.com/parler-haut-interagir-librement/ux-kit

## Tâches 🧰

`castor` liste toutes les tâches. Les tests, la couverture, les vérifications, les correcteurs, l'intégration continue
locale et les crochets git sont les tâches communes de [`phil/dev-tools`](https://packagist.org/packages/phil/dev-tools),
que [castor.php](castor.php) importe depuis `vendor/phil/dev-tools/resources/castor/tasks.php` et règle par sa constante
`PHIL_CASTOR` ; le fichier garde les tâches propres au gabarit — les serveurs, la base de données, les environnements,
`test:schema` et `lint:doctrine`. Lancez `composer install` avant la première tâche. Les principales :

| Tâche | Ce qu'elle fait |
|---|---|
| `castor start`, `castor stop` | Le serveur web du binaire Symfony avec SQLite et ses migrations. |
| `castor up`, `castor down` | FrankenPHP en mode worker, PostgreSQL et Mailpit, avec Docker Compose. |
| `castor secret` | Un `APP_SECRET` aléatoire dans `.env.local`, s'il n'en contient pas. |
| `castor reset-database` | La base SQLite remise à zéro et migrée. |
| `castor load-fixtures` | La base SQLite remise à zéro et migrée, avec les comptes `admin` et `member` et dix autres. |
| `castor test` | Toutes les suites PHPUnit ; `--filter` et `--options` passent à PHPUnit. |
| `castor test:unit`, `test:integration`, `test:api`, `test:functional`, `test:e2e` | Une suite ; `castor test:suite <nom>` lance n'importe quelle suite de `phpunit.dist.xml`. |
| `castor test:next` | Les tests sur la prochaine version de PHP, dans son image Docker. |
| `castor test:schema` | Les migrations sur une base SQLite neuve, puis son schéma comparé au mappage Doctrine. |
| `castor coverage` | Le rapport de couverture HTML dans `var/coverage/`, et son seuil. |
| `castor infection` | Les tests de mutation. |
| `castor lint` | Toutes les vérifications et l'analyse statique, puis `lint:doctrine`. |
| `castor fix` | Rector, PHP-CS-Fixer, Twig-CS-Fixer, Biome et composer-normalize. |
| `castor ci` | L'intégration continue, en local : la couverture, toutes les vérifications, puis `test:schema`. |
| `castor hooks` | Les crochets git : les vérifications rapides des fichiers indexés avant chaque commit. |

## Portes de qualité 🚦

Les vérifications viennent de [`phil/dev-tools`](https://packagist.org/packages/phil/dev-tools), l'outillage commun de
PHIL, adapté à une application. Chaque outil est installé dans son propre répertoire `vendor-bin/<outil>/` par le
[Composer bin plugin](https://github.com/bamarni/composer-bin-plugin), pour que ses dépendances ne rencontrent jamais
celles de l'application. Les fichiers de configuration minces de la racine (`phpstan.neon`, `rector.php`,
`.php-cs-fixer.dist.php`, `arkitect.php`) appellent les configurations communes ; `phil-dev.php` contient ce qui est
propre à l'application, chaque exception avec sa raison.

| Tâche | Vérifie |
|---|---|
| `castor lint:composer` | L'analyse des dépendances (une classe utilisée sans que son paquet soit requis), `composer audit`, composer-unused et composer-normalize. |
| `castor lint:php` | PHP-CS-Fixer : `@Symfony`, `@Symfony:risky`, types stricts et comparaisons strictes. |
| `castor lint:twig` | `lint:twig`, puis le style des gabarits. |
| `castor lint:js` | Biome sur les contrôleurs Stimulus. |
| `castor lint:yaml`, `lint:container`, `lint:doctrine` | Les fichiers YAML (`config/` compris), le conteneur et le mappage Doctrine. |
| `castor stan` | PHPStan au niveau maximal, avec une couverture des types à 100 %, une limite de complexité cognitive et des appels interdits. |
| `castor lint:rector` | Rector n'a plus rien à changer. |
| `castor lint:architecture` | Arkitect sur la forme des classes ; Deptrac sur les couches DDD et les bibliothèques que chacune peut utiliser. |

## Ce qu'il ne livre pas 🙅

* Aucun fichier de déploiement : chaque site apporte le sien.
* Aucune connexion par un fournisseur d'identité : c'est une [étape facultative](#étapes-facultatives-).
* Aucun document légal publié ni déclaration d'accessibilité : le site les publie quand ils disent quelque chose de vrai.
* Aucune compilation Node.js : AssetMapper sert le JavaScript et phil-ux tels quels.

## Références 📚

* [Introducing the MicroSymfony application template](https://www.strangebuzz.com/en/blog/introducing-the-microsymfony-application-template) (strangebuzz.com)
* [MicroSymfony 8: ready for the LTS promise](https://www.strangebuzz.com/en/blog/microsymfony-8-ready-for-the-lts-promise) (strangebuzz.com)
* [Organizing your Symfony project tests](https://www.strangebuzz.com/en/blog/organizing-your-symfony-project-tests) (strangebuzz.com)
* [A better ADR pattern for your Symfony controllers](https://www.strangebuzz.com/en/blog/a-better-adr-pattern-for-your-symfony-controllers) (strangebuzz.com)
* [Castor, a journey across the sea of task runners](https://jolicode.com/blog/castor-a-journey-across-the-sea-of-task-runners) (jolicode.com)
* [Messenger: sync and queued message handling](https://symfony.com/doc/current/messenger.html) (symfony.com)
* [Localized routes](https://symfony.com/doc/current/routing.html#localized-routes-i18n) (symfony.com)
* [The Symfony Framework Best Practices](https://symfony.com/doc/current/best_practices.html) (symfony.com)

## Les autres gabarits « **Parler** Haut, **Interagir** Librement » 🩻

* [MicroSymfony](https://github.com/parler-haut-interagir-librement/MicroSymfony) (`phil/microsymfony`) : le gabarit
  micro, sans base de données, avec Pico CSS, Stimulus et Turbo, Castor, les tests et les portes de qualité.
* [WebSymfony](https://github.com/parler-haut-interagir-librement/WebSymfony) (`phil/websymfony`) : MicroSymfony et ce
  dont une application web a besoin — Doctrine, des comptes de membres, des en-têtes de sécurité, une administration,
  le français et l'anglais —, dans la disposition habituelle de Symfony et sur Pico CSS.
* [ApiSymfony](https://github.com/parler-haut-interagir-librement/ApiSymfony) (`phil/apisymfony`) : une API avec
  [API Platform](https://api-platform.com), Castor, les tests et les portes de qualité.

## Contribuer 🤝

Voir [CONTRIBUTING](CONTRIBUTING.md) et le [code de conduite](CODE_OF_CONDUCT.md).

## Sécurité 🧯

Voir [SECURITY](SECURITY.md).

## Crédits 🙏

* [Philippe Gamache](https://ph-il.ca), mainteneur, pour [Parler Haut, Interagir Librement](https://ph-il.ca)
* [COil](https://github.com/COil), auteur de [strangebuzz/MicroSymfony](https://github.com/strangebuzz/MicroSymfony),
  et [ses contributeurs](https://github.com/strangebuzz/MicroSymfony/graphs/contributors)
* [Tous les contributeurs de WebSkeleton](https://github.com/parler-haut-interagir-librement/WebSkeleton/graphs/contributors)

## Licence ⚖️

La licence MIT, avec l'attribution au dépôt d'origine qu'elle demande. Voir le [fichier de licence](LICENSE).

## Bâti avec WebSkeleton 🛠️

* IRL Assemble Online
* [La Page à Melkor](https://lapageamelkor.org)
* [Parler Haut, Interagir Librement](https://ph-il.ca)
* RPG Assemble Online
* [TiltTek](https://tilttek.com)
* [TTRPG Maker](https://ttrpgmaker.com)
