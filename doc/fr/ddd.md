<!-- phil-dev source-sha256=c5744c6e6d4ed0d79194d81e70f54bfce30c8457358e9f9cadcdc5b102523cba -->
# Identifiants et DDD Maker

WebSkeleton est organisé par contexte délimité, `src/<Context>/{Domain,Application,Infrastructure}`, sur
[`phil/ddd-bundle`](https://github.com/parler-haut-interagir-librement/ddd-bundle). Cette page montre deux de ses outils
dans le gabarit : les identifiants typés, de la colonne jusqu'au contrôleur et au JSON, et les commandes `make:ddd:*`
qui écrivent un contexte et ses classes selon cette disposition.

| | Où |
|---|---|
| L'identifiant de l'exemple | [AccountId](../../src/Account/Domain/AccountId.php), dans le domaine de `Account` |
| Son type DBAL | [AccountIdType](../../src/Account/Infrastructure/Persistence/Doctrine/Type/AccountIdType.php), déclaré sous le nom `account_id` dans [doctrine.yaml](../../config/packages/doctrine.yaml) |
| Un contrôleur qui le reçoit | [ShowAccountAction](../../src/Account/Infrastructure/Http/Api/ShowAccountAction.php), `GET /api/accounts/{account}` |
| Une vue que le Serializer écrit | [AccountView](../../src/Account/Application/AccountView.php), la réponse de [ShowAccountQuery](../../src/Account/Application/ShowAccountQuery.php) |
| Les commandes | `bin/console make:ddd:*`, avec `symfony/maker-bundle`, dans l'environnement de développement seulement |

## Identifiants typés

Un identifiant de `phil/ddd-bundle` est un UUIDv7 de sa propre classe : un `AccountId` n'est jamais confondu avec
l'identifiant d'un autre agrégat, même quand les deux portent le même UUID. Le domaine le déclare, vide :

```php
namespace App\Account\Domain;

use Phil\DddBundle\Domain\Identifier\Identifier;

final readonly class AccountId extends Identifier
{
}
```

Un nouveau compte prend `AccountId::generate()` ; une chaîne venue de l'extérieur en devient un par
`AccountId::fromString()`, qui lance l'`InvalidValue` du bundle pour tout ce qui n'est pas un UUIDv7.

### Dans la base de données

Chaque identifiant a son type DBAL, dans l'infrastructure de son contexte, qui nomme la classe que la colonne relit :

```php
namespace App\Account\Infrastructure\Persistence\Doctrine\Type;

use App\Account\Domain\AccountId;
use Phil\DddBundle\Infrastructure\Doctrine\IdentifierType;

/** @extends IdentifierType<AccountId> */
final class AccountIdType extends IdentifierType
{
    public const string NAME = 'account_id';

    protected function identifierClass(): string
    {
        return AccountId::class;
    }
}
```

Le type est déclaré dans `config/packages/doctrine.yaml`, sous un nom fait de la classe en snake case :

```yaml
doctrine:
    dbal:
        types:
            account_id: App\Account\Infrastructure\Persistence\Doctrine\Type\AccountIdType
```

et l'entité y associe sa colonne, par des attributs :

```php
#[ORM\Id]
#[ORM\Column(type: AccountIdType::NAME, unique: true)]
private AccountId $id;
```

La colonne est un `UUID` natif sur PostgreSQL et 16 octets sur MySQL et SQLite, la même colonne que déclare le
`UuidType` de Symfony : passer une entité d'un `Uuid` à un identifiant n'exige aucune migration.

Le type prend seulement un identifiant de sa classe, jamais une chaîne, pour qu'une valeur ne puisse pas contourner la
vérification de classe. Un appelant qui détient une chaîne la transforme d'abord en identifiant. L'administration ne le
fait pas : EasyAdmin trouve une entité par la chaîne de son adresse.
[UserRepository](../../src/Account/Infrastructure/Persistence/Doctrine/UserRepository.php), le dépôt de `User`,
redéfinit `find()` pour accepter aussi la chaîne canonique d'un `AccountId`, et ne trouve rien pour toute autre chaîne.
Une entité dont l'administration montre l'identifiant a besoin d'un dépôt du même genre.

### Dans un contrôleur

Un argument de contrôleur typé avec une classe d'identifiant, et nommé comme le paramètre de la route, reçoit cette
classe :

```php
#[Route(path: '/accounts/{account}', name: self::class, methods: ['GET'])]
public function __invoke(AccountId $account): JsonResponse
{
    $view = $this->queryBus->ask(new ShowAccountQuery($account));
    // …
}
```

Le résolveur de valeur du bundle le construit avec `AccountId::fromString()`. Tout autre contenu du paramètre — pas un
UUID, un UUID d'une autre version — est un 404 avant que le contrôleur s'exécute ; un identifiant qui ne nomme aucun
compte est aussi un 404, venu du contrôleur. Aucune entité n'est chargée pour résoudre l'argument : le contrôleur
demande au bus de requêtes ce qu'il montre.

Le paramètre est la chaîne canonique de l'identifiant. Il convient à une API ou à l'administration, comme ici, où
`/api/accounts/{account}` est réservé à `ROLE_ADMIN`. Une page publique qui ne doit pas montrer l'identifiant tel quel
le convertit dans la forme qu'elle choisit, et retire son argument du résolveur par
`#[ValueResolver(IdentifierValueResolver::class, disabled: true)]`.

### En JSON

Avec `symfony/serializer`, que le gabarit requiert, le bundle enregistre un normaliseur : un identifiant s'écrit sous
sa chaîne canonique et se reconstruit dans la classe qu'une propriété déclare.

```json
{"id":"019abf3c-8a1e-7b2c-9d3e-4f5a6b7c8d9e","username":"ada","roles":["ROLE_USER"],"createdAt":"2026-11-26T10:00:00+00:00"}
```

`AccountView` est la réponse de `ShowAccountQuery` ; l'adresse courriel et le mot de passe n'y sont pas.
`$serializer->deserialize($json, AccountView::class, 'json')` redonne un `AccountView` dont l'`id` est un `AccountId` ;
avec `#[MapRequestPayload]`, un champ qui n'est pas un UUIDv7 donne un 422, comme tout autre champ.

### Pour phil/privacy-bundle

Le bundle de confidentialité connaît un membre par une chaîne : `User::dataSubjectIdentifier()` est la chaîne de son
`AccountId`, et [AccountPersonalData](../../src/Account/Infrastructure/Privacy/AccountPersonalData.php) la retransforme
en `AccountId` ; toute autre chaîne ne nomme aucun compte, et l'exportation et l'effacement ne trouvent rien.

## Le DDD Maker

`symfony/maker-bundle` est une dépendance de développement du gabarit, enregistrée dans l'environnement `dev` seulement.
Avec lui, `phil/ddd-bundle` ajoute cinq commandes :

| Commande | Écrit, sous `src/<Context>/` |
|---|---|
| `make:ddd:context <Context>` | `README.md`, et `Domain/`, `Application/`, `Infrastructure/`, chacun gardé par un `.gitignore` vide |
| `make:ddd:identifier <Context> <Name>` | `Domain/<Name>Id.php`, et son type `Infrastructure/Persistence/Doctrine/Type/<Name>IdType.php` |
| `make:ddd:command <Context> <Name>` | `Application/<Name>Command.php` et son gestionnaire `Application/<Name>Handler.php` |
| `make:ddd:query <Context> <Name>` | `Application/<Name>Query.php` et son gestionnaire `Application/<Name>Handler.php` |
| `make:ddd:event <Context> <Name>` | `Domain/Event/<Name>.php`, un événement de domaine |

Un contexte et un nom sont des mots en PascalCase ; un argument manquant est demandé ; un fichier existant n'est jamais
écrasé. `AccountId`, `AccountIdType`, `ShowAccountQuery` et `ShowAccountHandler` du gabarit ont d'abord été écrits par
ces commandes. La référence complète est la [page du maker dans le bundle](https://github.com/parler-haut-interagir-librement/ddd-bundle/blob/main/doc/maker.md).

### Un nouveau contexte, pas à pas

```console
$ bin/console make:ddd:context Catalog
$ bin/console make:ddd:identifier Catalog Product
$ bin/console make:ddd:command Catalog AddProduct
$ bin/console make:ddd:query Catalog FindProduct
$ bin/console make:ddd:event Catalog ProductAdded
```

```text
src/Catalog/
├── README.md
├── Domain/
│   ├── ProductId.php
│   └── Event/
│       └── ProductAdded.php
├── Application/
│   ├── AddProductCommand.php
│   ├── AddProductHandler.php
│   ├── FindProductQuery.php
│   └── FindProductHandler.php
└── Infrastructure/
    └── Persistence/Doctrine/Type/
        └── ProductIdType.php
```

Les services, les routes et les bus n'ont besoin de rien de plus : [services.yaml](../../config/services.yaml) charge
`src/`, écarte chaque `Domain/` ainsi que les commandes, les requêtes et les vues de chaque `Application/`, et le
bundle étiquette chaque gestionnaire sur son bus. Ce qui reste est de la configuration, en YAML :

1. **Le type DBAL.** La commande affiche la ligne pour un `config/packages/doctrine.php` ; ce gabarit configure
   Doctrine en YAML, donc le type va dans [doctrine.yaml](../../config/packages/doctrine.yaml) :

   ```yaml
   doctrine:
       dbal:
           types:
               product_id: App\Catalog\Infrastructure\Persistence\Doctrine\Type\ProductIdType
   ```

2. **Le mapping Doctrine**, une fois que le contexte a des entités, dans le même fichier, comme celui de `Account` :

   ```yaml
   doctrine:
       orm:
           mappings:
               Catalog:
                   type: attribute
                   is_bundle: false
                   dir: '%kernel.project_dir%/src/Catalog/Infrastructure/Persistence/Doctrine/Entity'
                   prefix: 'App\Catalog\Infrastructure\Persistence\Doctrine\Entity'
   ```

3. **Deptrac** : `'Catalog'` ajouté à `$contexts` dans [phil-dev.php](../../phil-dev.php), ce qui donne au contexte ses
   trois couches et leurs règles ; `castor lint:architecture` les vérifie.
4. **Les ports** : chaque interface du domaine ou de l'application reçoit son alias vers son adaptateur dans
   [services.yaml](../../config/services.yaml), à côté de `AccountViews`.
5. **La requête** : `make:ddd:query` écrit `@implements Query<null>` et un gestionnaire qui retourne `null` ; les deux
   prennent le type de la réponse, comme `ShowAccountQuery` prend `AccountView|null`.

Chaque fichier PHP écrit passe PHPStan à son niveau maximal et le style de code du gabarit tel quel. Le seuil de
couverture de `castor ci` est de 100 % : les nouvelles classes ont besoin de leurs tests avant qu'il soit de nouveau
vert.
