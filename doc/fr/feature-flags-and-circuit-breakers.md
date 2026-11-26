<!-- phil-dev source-sha256=73f0d2a4525ec7f5cb997550d6ebede36912b88a4b3fd12e33d96306bdf0b8bc -->
# Indicateurs de fonctionnalité et disjoncteurs

Chaque site bâti sur WebSkeleton est livré avec deux interrupteurs de sécurité.
[`phil/feature-flags-bundle`](https://github.com/parler-haut-interagir-librement/feature-flags-bundle) place une
nouvelle fonctionnalité derrière un indicateur, éteint jusqu'à ce qu'un administrateur l'allume, et la retire de nouveau
sans déploiement. [`phil/circuit-breaker-bundle`](https://github.com/parler-haut-interagir-librement/circuit-breaker-bundle)
cesse d'appeler un tiers qui échoue : une page répond aussitôt au lieu d'attendre un serveur en panne. Les deux
travaillent ensemble : chaque circuit a un indicateur qui le force à s'ouvrir.

| | Indicateurs de fonctionnalité | Disjoncteurs |
|---|---|---|
| Configuration | [phil_feature_flags.yaml](../../config/packages/phil_feature_flags.yaml) | [phil_circuit_breaker.yaml](../../config/packages/phil_circuit_breaker.yaml) |
| Où vit l'état | La base de données du site, tables `phil_feature_flags_definition` et `phil_feature_flags_state` | Le pool de cache `cache.circuits`, partagé par tous les workers |
| Qui le change | Un administrateur, sous `/admin/flags`, avec une raison | Le circuit lui-même, d'après les réponses qu'il compte ; un administrateur le force à s'ouvrir par son indicateur |
| Exemple du gabarit | `text.slug-page`, la page `/fr/slug` et son lien sur la page d'accueil | `http.example`, autour du client à portée `example.client` |

## Indicateurs de fonctionnalité

### Ce qui est branché

[phil_feature_flags.yaml](../../config/packages/phil_feature_flags.yaml) déclare les indicateurs du site et active :

- le **stockage en base de données** : les états réglés depuis l'administration et les indicateurs qui y sont créés sont
  gardés dans deux tables, que [la migration](../../migrations/Version20261126150000.php) crée sur SQLite, PostgreSQL et
  MySQL. Le bundle les ajoute au schéma que Doctrine génère : `doctrine:schema:validate` et `castor test:schema` les
  voient ;
- la **bascule auditée** : chaque changement est autorisé, porte une raison et est écrit dans le canal de journal
  `feature_flags` avant de s'appliquer ;
- les **écrans** sous `/admin/flags`, reliés depuis le menu du tableau de bord et rendus dans la mise en page du site.

Quatre adaptateurs du contexte `Account`, dans
[src/Account/Infrastructure/FeatureFlags](../../src/Account/Infrastructure/FeatureFlags), relient le bundle aux comptes :

| Adaptateur | Port du bundle | Ce qu'il fait |
|---|---|---|
| `MemberContextResolver` | `EvaluationContextResolver` | Le membre connecté est le sujet des règles de ciblage ; un visiteur a un contexte vide, donc un indicateur ciblé est éteint pour lui. |
| `AdministratorToggleAuthorizer` | `ToggleAuthorizer` | Seul `ROLE_ADMIN` change un indicateur, depuis les écrans ou depuis tout autre appelant. |
| `LogToggleAuditor` | `ToggleAuditor` | Un avis du canal `feature_flags` par changement : l'administrateur, l'indicateur, avant et après, la raison, l'heure. En production, chaque ligne va à la sortie d'erreur standard en JSON. |
| `AdministratorActorProvider` | `ActorProvider` | Le nom d'utilisateur de l'administrateur, écrit dans la ligne d'audit. |

Les indicateurs sont évalués une fois par requête et par contexte, et les services du bundle sont réinitialisés entre
deux requêtes : ils ne gardent aucun état d'une requête à l'autre dans le worker de FrankenPHP.

### L'exemple : `text.slug-page`

L'indicateur garde une petite fonctionnalité du contexte `Text` : [SlugPageAction](../../src/Text/Infrastructure/Http/Controller/SlugPageAction.php),
la page du slug d'un titre, `/fr/slug` et `/en/slug`, qui fonctionne sans JavaScript. Il est déclaré éteint :

```yaml
# config/packages/phil_feature_flags.yaml
phil_feature_flags:
    flags:
        text.slug-page:
            active: false
            description: The page of the slug of a title, and its link on the home page.
            owner: text
            kind: release
            expires_on: '2027-11-26'
```

Il se lit à deux endroits, par la même évaluation :

- **dans le contrôleur**, par l'attribut du bundle : tant que l'indicateur est éteint, la page répond 404, comme une
  page qui n'existe pas encore ;

  ```php
  #[IsFeatureEnabled(self::FLAG)]
  final class SlugPageAction extends AbstractController
  ```

- **dans Twig**, sur la page d'accueil, qui ne renvoie à la page que tant que l'indicateur est allumé :

  ```twig
  {%- if feature_enabled('text.slug-page') %}
  <p><a href="{{ path('App\\Text\\Infrastructure\\Http\\Controller\\SlugPageAction') }}">{{ 'home.slugify.page'|trans }}</a></p>
  {%- endif %}
  ```

[SlugPageActionTest](../../tests/Functional/Text/SlugPageActionTest.php) teste les deux cas : éteint, la page répond 404
et la page d'accueil n'a aucun lien ; allumé par un administrateur depuis `/admin/flags`, les deux apparaissent.

### Où un indicateur se lit

Un indicateur se lit dans la couche **Application** ou **Infrastructure** d'un contexte, jamais dans son **Domain** : le
domaine contient de simples objets PHP et ne sait rien de ce qui est publié. Le bundle offre quatre endroits :

| Endroit | Couche | Pour |
|---|---|---|
| `#[IsFeatureEnabled('name')]` sur un contrôleur | Infrastructure | Une page ou un point d'entrée entier qui ne doit pas exister tant que l'indicateur est éteint. |
| `feature_enabled('name')` et `feature_variant('name')` dans Twig | Infrastructure (gabarits) | Un bloc de page montré ou caché. |
| `FlagEvaluator::isEnabled(FlagName::of('name'))` dans un service | Application ou Infrastructure | Un changement de comportement dans un handler ou un adaptateur. |
| Une condition de route, `service('feature_flags').enabled('name')` | Infrastructure | Un nouveau contrôleur sur le chemin de l'actuel ; elle exige `symfony/expression-language` et ne lit qu'un indicateur allumé ou éteint pour tout le monde. |

Un handler de la couche Application qui choisit entre deux comportements interroge l'évaluateur une fois, dans une
méthode nommée : le nom de l'indicateur vit à un seul endroit et son retrait ne change qu'une méthode.

### Ajouter un indicateur

1. Déclarez-le dans [phil_feature_flags.yaml](../../config/packages/phil_feature_flags.yaml), `active: false`, avec son
   responsable, son genre (`release`, `experiment`, `ops` ou `permission`) et la date à laquelle il devrait avoir disparu.
2. Lisez-le dans le contrôleur, le gabarit ou le service, comme ci-dessus.
3. Testez les deux côtés : chaque indicateur double les chemins dans le code qu'il garde.
4. Allumez-le sous `/admin/flags`, avec une raison. Un indicateur peut aussi y être créé, inactif, et ciblé sur
   certains membres (`subject`) ou sur une part d'entre eux, par un objet JSON de règles.

`bin/console phil:feature-flags:list` liste chaque indicateur, son état et sa source ; `--fail-on-expired` échoue quand
un indicateur a dépassé sa date d'expiration, ce qu'une tâche d'intégration continue peut lancer. Quand une
fonctionnalité est publiée pour de bon, l'indicateur, ses vérifications et son état stocké disparaissent.

### Sans les écrans

Un site sans administration éteint les écrans et la bascule, et garde les indicateurs de la configuration : les clés
`admin` et `toggle` s'en vont, avec `config/routes/phil_feature_flags.yaml`, le lien du tableau de bord et les
adaptateurs `AdministratorToggleAuthorizer`, `LogToggleAuditor` et `AdministratorActorProvider`. Un indicateur change
alors par déploiement. Un site qui garde le stockage en base de données sans les écrans allume un indicateur depuis une
commande de console à lui, par `FlagToggler`.

## Disjoncteurs

### Ce qui est branché

[phil_circuit_breaker.yaml](../../config/packages/phil_circuit_breaker.yaml) déclare un circuit par tiers. Un circuit
avec `http_client` place ce client derrière lui : tant que le circuit est ouvert, une requête est refusée avant de
partir par `CircuitOpen`, une `TransportExceptionInterface`, de sorte que le code qui gère déjà un hôte injoignable le
gère aussi. Une erreur de serveur (5xx) ou une erreur de transport compte comme un échec ; toute autre réponse, un 4xx
compris, compte comme un succès.

Les états des circuits sont gardés dans le pool de cache `cache.circuits` de [cache.yaml](../../config/packages/cache.yaml),
sur le système de fichiers comme `cache.app` : un circuit ouvert dans un worker est ouvert dans tous. En production, le
bundle refuse un pool qui reste sur chaque serveur, sauf si le site dit qu'il tourne sur un seul : le gabarit le dit,
avec `single_server: true`. Un site sur plusieurs serveurs donne à `cache.circuits` un adaptateur partagé, Redis par
exemple, et retire cette ligne.

### L'exemple : `http.example`

Le gabarit n'appelle aucun tiers : son circuit garde donc un client à portée d'exemple de
[framework.yaml](../../config/packages/framework.yaml), dont l'URI de base est `EXAMPLE_API_URL` :

```yaml
# config/packages/framework.yaml
framework:
    http_client:
        default_options:
            timeout: 10
        scoped_clients:
            example.client:
                base_uri: '%env(EXAMPLE_API_URL)%'
                timeout: 5
```

```yaml
# config/packages/phil_circuit_breaker.yaml
phil_circuit_breaker:
    cache_pool: cache.circuits
    circuits:
        http.example:
            failure_rate_percent: 50   # half the calls of a window failed...
            minimum_calls: 5           # ...out of five at least...
            window_seconds: 60         # ...within a minute,
            open_seconds: 30           # and the client is not called for thirty seconds
            slow_call_milliseconds: 5000
            http_client: example.client
```

Un service reçoit le client par son alias d'autowiring nommé, derrière son circuit, et donne sa solution de repli quand
le circuit est ouvert :

```php
use Phil\CircuitBreakerBundle\Infrastructure\HttpClient\Exception\CircuitOpen;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class WeatherForecasts
{
    public function __construct(private HttpClientInterface $exampleClient) {}

    public function today(): ?array
    {
        try {
            return $this->exampleClient->request('GET', '/forecast')->toArray();
        } catch (CircuitOpen) {
            return null;    // the circuit is open: the request was not sent
        }
    }
}
```

[ExampleCircuitTest](../../tests/Integration/Shared/Infrastructure/HttpClient/ExampleCircuitTest.php) répond à cinq
requêtes par 503 et vérifie que la sixième est refusée avant de partir. Dans l'environnement de test, aucune requête ne
quitte la machine : chaque client répond depuis [MockResponses](../../tests/Support/MockResponses.php), réglé comme
`framework.http_client.mock_response_factory`, dont un test choisit le statut.

Un site renomme le client et le circuit d'après le tiers qu'il appelle (`payments.client` et `http.payments`, par
exemple), règle l'URI de base et ajoute un circuit par autre tiers, pour qu'un partenaire en panne ne fasse jamais
déclencher les autres. Un site qui n'en appelle aucun retire `example.client`, `http.example` et `EXAMPLE_API_URL`.

### Forcer l'ouverture d'un circuit

Chaque circuit a un indicateur, `circuit-open.<key>` (ici `circuit-open.http.example`), inactif, de genre `ops`. Un
administrateur qui sait qu'un tiers est en panne l'allume sous `/admin/flags`, avec une raison : dès la requête
suivante, le circuit refuse tous les appels, quels que soient ses compteurs, jusqu'à ce que l'indicateur soit éteint.
Déclarer `circuit-open.http.example: true` dans `phil_feature_flags.yaml` le force plutôt à s'ouvrir par déploiement.

Un appel qui n'est pas une requête HTTP (un service de courriel, un SDK) est gardé de la même façon par
`CircuitBreakers` : demandez `isAvailable()`, puis rapportez le résultat par `record()`. Le profileur montre l'état de
chaque circuit, et les indicateurs qu'une requête a évalués, à la fin de la requête.

## Références

- La documentation de [`phil/feature-flags-bundle`](https://github.com/parler-haut-interagir-librement/feature-flags-bundle) :
  `vendor/phil/feature-flags-bundle/doc/`, de sa configuration à ses règles de ciblage et à ses tests A/B.
- La documentation de [`phil/circuit-breaker-bundle`](https://github.com/parler-haut-interagir-librement/circuit-breaker-bundle) :
  `vendor/phil/circuit-breaker-bundle/doc/`, et celle de `phil/circuit-breaker` pour la machine à états.
- [Le client HTTP](https://symfony.com/doc/current/http_client.html) et ses clients à portée (symfony.com).
