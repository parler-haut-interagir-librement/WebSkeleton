# Identifiers and the DDD Maker

WebSkeleton is organised by bounded context, `src/<Context>/{Domain,Application,Infrastructure}`, on
[`phil/ddd-bundle`](https://github.com/parler-haut-interagir-librement/ddd-bundle). This page shows two of its tools in
the template: the typed identifiers, from the column to the controller and the JSON, and the `make:ddd:*` commands
that write a context and its classes in this layout.

| | Where |
|---|---|
| The identifier of the example | [AccountId](../src/Account/Domain/AccountId.php), in the domain of `Account` |
| Its DBAL type | [AccountIdType](../src/Account/Infrastructure/Persistence/Doctrine/Type/AccountIdType.php), registered as `account_id` in [doctrine.yaml](../config/packages/doctrine.yaml) |
| A controller that receives it | [ShowAccountAction](../src/Account/Infrastructure/Http/Api/ShowAccountAction.php), `GET /api/accounts/{account}` |
| A view the Serializer writes | [AccountView](../src/Account/Application/AccountView.php), the answer of [ShowAccountQuery](../src/Account/Application/ShowAccountQuery.php) |
| The commands | `bin/console make:ddd:*`, with `symfony/maker-bundle`, in the development environment only |

## Typed identifiers

An identifier of `phil/ddd-bundle` is a UUIDv7 of its own class: an `AccountId` is never mistaken for the identifier
of another aggregate, even when both carry the same UUID. The domain declares it, empty:

```php
namespace App\Account\Domain;

use Phil\DddBundle\Domain\Identifier\Identifier;

final readonly class AccountId extends Identifier
{
}
```

A new account takes `AccountId::generate()`; a string from outside becomes one with `AccountId::fromString()`, which
throws the `InvalidValue` of the bundle for anything but a UUIDv7.

### In the database

Each identifier has its DBAL type, in the infrastructure of its context, which names the class the column reads back:

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

The type is registered in `config/packages/doctrine.yaml`, under a name made of the class in snake case:

```yaml
doctrine:
    dbal:
        types:
            account_id: App\Account\Infrastructure\Persistence\Doctrine\Type\AccountIdType
```

and the entity maps its column with it, by attributes:

```php
#[ORM\Id]
#[ORM\Column(type: AccountIdType::NAME, unique: true)]
private AccountId $id;
```

The column is a native `UUID` on PostgreSQL and 16 bytes on MySQL and SQLite, the same column Symfony's `UuidType`
declares: moving an entity from a `Uuid` to an identifier needs no migration.

The type takes an identifier of its class only, never a string, so that a value cannot bypass the class check. A
caller that holds a string turns it into an identifier first. The administration does not: EasyAdmin finds an entity
by the string of its URL. [UserRepository](../src/Account/Infrastructure/Persistence/Doctrine/UserRepository.php), the
repository of `User`, overrides `find()` to accept the canonical string of an `AccountId` as well, and finds nothing
for any other string. An entity with an identifier that the administration shows needs a repository of the same kind.

### In a controller

A controller argument typed with an identifier class, and named like the route placeholder, receives that class:

```php
#[Route(path: '/accounts/{account}', name: self::class, methods: ['GET'])]
public function __invoke(AccountId $account): JsonResponse
{
    $view = $this->queryBus->ask(new ShowAccountQuery($account));
    // …
}
```

The value resolver of the bundle builds it with `AccountId::fromString()`. Anything else in the placeholder — not a
UUID, a UUID of another version — is a 404 before the controller runs; an identifier that names no account is a 404
too, from the controller. No entity is loaded to resolve the argument: the controller asks the query bus for what it
shows.

The placeholder is the canonical string of the identifier. It suits an API or the administration, as here, where
`/api/accounts/{account}` is for `ROLE_ADMIN` only. A public page that must not show the identifier as it is maps it to
the form it chooses, and opts its argument out of the resolver with
`#[ValueResolver(IdentifierValueResolver::class, disabled: true)]`.

### In JSON

With `symfony/serializer`, which the template requires, the bundle registers a normaliser: an identifier is written as
its canonical string and built back to the class a property declares.

```json
{"id":"019abf3c-8a1e-7b2c-9d3e-4f5a6b7c8d9e","username":"ada","roles":["ROLE_USER"],"createdAt":"2026-11-26T10:00:00+00:00"}
```

`AccountView` is the answer of `ShowAccountQuery`; the email address and the password are not in it.
`$serializer->deserialize($json, AccountView::class, 'json')` gives back an `AccountView` whose `id` is an `AccountId`;
with `#[MapRequestPayload]`, a field that is not a UUIDv7 is a 422, as for any other field.

### For phil/privacy-bundle

The privacy bundle knows a member by a string: `User::dataSubjectIdentifier()` is the string of their `AccountId`, and
[AccountPersonalData](../src/Account/Infrastructure/Privacy/AccountPersonalData.php) turns it back into an `AccountId`;
any other string names no account, and the export and the erasure find nothing.

## The DDD Maker

`symfony/maker-bundle` is a development requirement of the template, registered in the `dev` environment only. With
it, `phil/ddd-bundle` adds five commands:

| Command | Writes, under `src/<Context>/` |
|---|---|
| `make:ddd:context <Context>` | `README.md`, and `Domain/`, `Application/`, `Infrastructure/`, each kept by an empty `.gitignore` |
| `make:ddd:identifier <Context> <Name>` | `Domain/<Name>Id.php`, and its type `Infrastructure/Persistence/Doctrine/Type/<Name>IdType.php` |
| `make:ddd:command <Context> <Name>` | `Application/<Name>Command.php` and its handler `Application/<Name>Handler.php` |
| `make:ddd:query <Context> <Name>` | `Application/<Name>Query.php` and its handler `Application/<Name>Handler.php` |
| `make:ddd:event <Context> <Name>` | `Domain/Event/<Name>.php`, a domain event |

A context and a name are PascalCase words; a missing argument is asked for; an existing file is never overwritten.
`AccountId`, `AccountIdType`, `ShowAccountQuery` and `ShowAccountHandler` of the template were first written by these
commands. The full reference is the [maker page of the bundle](https://github.com/parler-haut-interagir-librement/ddd-bundle/blob/main/doc/maker.md).

### A new context, step by step

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

The services, the routes and the buses need nothing more: [services.yaml](../config/services.yaml) loads `src/`, leaves
out every `Domain/` and the commands, queries and views of every `Application/`, and the bundle tags each handler on
its bus. What is left is configuration, in YAML:

1. **The DBAL type.** The command prints the line for a `config/packages/doctrine.php`; this template configures
   Doctrine in YAML, so the type goes in [doctrine.yaml](../config/packages/doctrine.yaml):

   ```yaml
   doctrine:
       dbal:
           types:
               product_id: App\Catalog\Infrastructure\Persistence\Doctrine\Type\ProductIdType
   ```

2. **The Doctrine mapping**, once the context has entities, in the same file, like the `Account` one:

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

3. **Deptrac**: `'Catalog'` added to `$contexts` in [phil-dev.php](../phil-dev.php), which gives the context its three
   layers and their rules; `castor lint:architecture` checks them.
4. **The ports**: each interface of the domain or the application gets its alias to its adapter in
   [services.yaml](../config/services.yaml), next to `AccountViews`.
5. **The query**: `make:ddd:query` writes `@implements Query<null>` and a handler that returns `null`; both take the
   type of the answer, as `ShowAccountQuery` takes `AccountView|null`.

Every PHP file written passes PHPStan at its maximum level and the code style of the template as it is. The coverage
threshold of `castor ci` is 100 %: the new classes need their tests before it is green again.
