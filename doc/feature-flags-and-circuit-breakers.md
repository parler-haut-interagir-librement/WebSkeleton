# Feature flags and circuit breakers

Every site built on WebSkeleton ships with two safety switches.
[`phil/feature-flags-bundle`](https://github.com/parler-haut-interagir-librement/feature-flags-bundle) puts a new
feature behind a flag, off until an administrator turns it on, and withdraws it again without a deploy.
[`phil/circuit-breaker-bundle`](https://github.com/parler-haut-interagir-librement/circuit-breaker-bundle) stops
calling a third party that fails, so a page answers at once instead of waiting on a server that is down. They work
together: each circuit has a flag that forces it open.

| | Feature flags | Circuit breakers |
|---|---|---|
| Configuration | [phil_feature_flags.yaml](../config/packages/phil_feature_flags.yaml) | [phil_circuit_breaker.yaml](../config/packages/phil_circuit_breaker.yaml) |
| Where the state lives | The database of the site, tables `phil_feature_flags_definition` and `phil_feature_flags_state` | The cache pool `cache.circuits`, shared by every worker |
| Who changes it | An administrator, under `/admin/flags`, with a reason | The circuit itself, from the answers it counts; an administrator forces it open with its flag |
| Example of the template | `text.slug-page`, the page `/fr/slug` and its link on the home page | `http.example`, around the scoped client `example.client` |

## Feature flags

### What is wired

[phil_feature_flags.yaml](../config/packages/phil_feature_flags.yaml) declares the flags of the site, and turns on:

- the **database store**: the states set from the administration and the flags created there are kept in two tables,
  which [the migration](../migrations/Version20261126150000.php) creates on SQLite, PostgreSQL and MySQL. The bundle
  adds them to the schema Doctrine generates, so `doctrine:schema:validate` and `castor test:schema` see them;
- the **audited toggle**: every change is authorized, carries a reason, and is written to the `feature_flags` log
  channel before it applies;
- the **screens** under `/admin/flags`, linked from the menu of the dashboard and rendered in the layout of the site.

Four adapters of the context `Account`, in
[src/Account/Infrastructure/FeatureFlags](../src/Account/Infrastructure/FeatureFlags), connect the bundle to the
accounts:

| Adapter | Port of the bundle | What it does |
|---|---|---|
| `MemberContextResolver` | `EvaluationContextResolver` | The member signed in is the subject of the targeting rules; a visitor has an empty context, so a targeted flag is off for them. |
| `AdministratorToggleAuthorizer` | `ToggleAuthorizer` | Only `ROLE_ADMIN` changes a flag, from the screens or from any other caller. |
| `LogToggleAuditor` | `ToggleAuditor` | One notice of the `feature_flags` channel per change: the administrator, the flag, before and after, the reason, the time. In production, every line goes to the standard error output as JSON. |
| `AdministratorActorProvider` | `ActorProvider` | The user name of the administrator, written in the audit line. |

The flags are evaluated once per request and context, and the services of the bundle are reset between two requests,
so they hold no state from one request to the next in the worker of FrankenPHP.

### The example: `text.slug-page`

The flag guards a small feature of the context `Text`: [SlugPageAction](../src/Text/Infrastructure/Http/Controller/SlugPageAction.php),
the page of the slug of a title, `/fr/slug` and `/en/slug`, which works without JavaScript. It is declared off:

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

It is read in two places, through the same evaluation:

- **in the controller**, by the attribute of the bundle: while the flag is off, the page answers 404, as a page that
  does not exist yet;

  ```php
  #[IsFeatureEnabled(self::FLAG)]
  final class SlugPageAction extends AbstractController
  ```

- **in Twig**, on the home page, which links to the page only while the flag is on:

  ```twig
  {%- if feature_enabled('text.slug-page') %}
  <p><a href="{{ path('App\\Text\\Infrastructure\\Http\\Controller\\SlugPageAction') }}">{{ 'home.slugify.page'|trans }}</a></p>
  {%- endif %}
  ```

[SlugPageActionTest](../tests/Functional/Text/SlugPageActionTest.php) tests both ways: off, the page answers 404 and
the home page has no link; turned on by an administrator from `/admin/flags`, both appear.

### Where a flag is read

A flag is read in the **Application** or the **Infrastructure** layer of a context, never in its **Domain**: the domain
holds plain PHP objects and knows nothing of what is released. The bundle gives four places:

| Place | Layer | For |
|---|---|---|
| `#[IsFeatureEnabled('name')]` on a controller | Infrastructure | A whole page or endpoint that must not exist while the flag is off. |
| `feature_enabled('name')` and `feature_variant('name')` in Twig | Infrastructure (templates) | A block of a page shown or hidden. |
| `FlagEvaluator::isEnabled(FlagName::of('name'))` in a service | Application or Infrastructure | A change of behaviour inside a handler or an adapter. |
| A route condition, `service('feature_flags').enabled('name')` | Infrastructure | A new controller on the path of the current one; it needs `symfony/expression-language`, and reads only a flag on or off for everyone. |

A handler of the Application layer that chooses between two behaviours asks the evaluator once, in a named method,
so that the name of the flag lives in one place and its removal changes one method.

### Adding a flag

1. Declare it in [phil_feature_flags.yaml](../config/packages/phil_feature_flags.yaml), `active: false`, with its
   owner, its kind (`release`, `experiment`, `ops` or `permission`) and the date it should be gone by.
2. Read it in the controller, the template or the service, as above.
3. Test both sides: each flag doubles the paths through the code it guards.
4. Turn it on under `/admin/flags`, with a reason. A flag can also be created there, inactive, and targeted at some
   members (`subject`) or a share of them, as a JSON object of rules.

`bin/console phil:feature-flags:list` lists every flag, its state and its source; `--fail-on-expired` fails when a
flag has outlived its expiry date, which a CI job can run. Once a feature is released for good, the flag, its checks
and its stored state go.

### Without the screens

A site without an administration turns the screens and the toggle off, and keeps the flags of the configuration: the
`admin` and `toggle` keys go, with `config/routes/phil_feature_flags.yaml`, the link of the dashboard and the
adapters `AdministratorToggleAuthorizer`, `LogToggleAuditor` and `AdministratorActorProvider`. A flag then changes by
deploy. A site that keeps the database store without the screens turns a flag on from a console command of its own,
through `FlagToggler`.

## Circuit breakers

### What is wired

[phil_circuit_breaker.yaml](../config/packages/phil_circuit_breaker.yaml) declares one circuit per third party. A
circuit with `http_client` puts that client behind it: while the circuit is open, a request is refused before it
leaves with `CircuitOpen`, a `TransportExceptionInterface`, so the code that already handles an unreachable host
handles it too. A server error (5xx) or a transport error counts as a failure; any other answer, a 4xx included,
counts as a success.

The states of the circuits are kept in the cache pool `cache.circuits` of [cache.yaml](../config/packages/cache.yaml),
on the filesystem like `cache.app`, so a circuit open in one worker is open in all. In production, the bundle refuses a
pool that stays on each server unless the site says it runs on one: the template does, with `single_server: true`. A
site on several servers gives `cache.circuits` a shared adapter, Redis for instance, and removes that line.

### The example: `http.example`

The template calls no third party, so its circuit guards an example scoped client of
[framework.yaml](../config/packages/framework.yaml), whose base URI is `EXAMPLE_API_URL`:

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

A service receives the client by its named autowiring alias, behind its circuit, and gives its fallback when the
circuit is open:

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

[ExampleCircuitTest](../tests/Integration/Shared/Infrastructure/HttpClient/ExampleCircuitTest.php) answers five
requests with 503 and checks that the sixth is refused before it leaves. In the test environment, no request leaves
the machine: every client answers from [MockResponses](../tests/Support/MockResponses.php), set as
`framework.http_client.mock_response_factory`, whose status a test chooses.

A site renames the client and the circuit after the third party it calls (`payments.client` and `http.payments`, say),
sets the base URI, and adds one circuit per other third party, so that one failing partner never trips the others. A
site that calls none removes `example.client`, `http.example` and `EXAMPLE_API_URL`.

### Forcing a circuit open

Each circuit has a flag, `circuit-open.<key>` (`circuit-open.http.example` here), inactive, of kind `ops`. An
administrator who knows a third party is down turns it on under `/admin/flags`, with a reason: from the next request,
the circuit refuses every call, whatever its counters say, until the flag is turned off. Declaring
`circuit-open.http.example: true` in `phil_feature_flags.yaml` forces it open by deploy instead.

A call that is not an HTTP request (a mailer, an SDK) is guarded the same way through `CircuitBreakers`: ask
`isAvailable()`, then report the outcome with `record()`. The profiler shows the state of each circuit, and the flags a
request evaluated, at the end of the request.

## References

- The documentation of [`phil/feature-flags-bundle`](https://github.com/parler-haut-interagir-librement/feature-flags-bundle):
  `vendor/phil/feature-flags-bundle/doc/`, from its configuration to its targeting rules and A/B tests.
- The documentation of [`phil/circuit-breaker-bundle`](https://github.com/parler-haut-interagir-librement/circuit-breaker-bundle):
  `vendor/phil/circuit-breaker-bundle/doc/`, and that of `phil/circuit-breaker` for the state machine.
- [The HTTP Client](https://symfony.com/doc/current/http_client.html) and its scoped clients (symfony.com).
