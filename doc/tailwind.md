# Tailwind instead of PHIL UX CSS

WebSkeleton is dressed by phil-ux, the stylesheet of `phil/ux-css`, and the recipes of the PHIL UX kit. A site that
prefers [Tailwind CSS](https://tailwindcss.com) switches to the Shadcn kit of the
[Symfony UX Toolkit](https://symfony.com/bundles/ux-toolkit/current/index.html) and to
[`phil/ux-kit-shadcn`](https://github.com/parler-haut-interagir-librement/ux-kit-shadcn), the PHIL UX recipes written
in Tailwind under the same names and props. Tailwind is built by
[`symfonycasts/tailwind-bundle`](https://symfony.com/bundles/TailwindBundle/current/index.html) on AssetMapper: still
no Node.js, no Webpack.

The steps below are made once, on a new site, before its own pages are written. PHIL UX stays the default of the
template.

## What changes

| | PHIL UX (default) | Tailwind |
|---|---|---|
| The stylesheet | phil-ux, served from `vendor/phil/ux-css/dist` as `phil-ux/phil-ux.css` | `assets/styles/app.css`, built by the Tailwind binary of `symfonycasts/tailwind-bundle` |
| `Badge`, `Button`, `Card`, `Table` | The PHIL UX kit (`phil/ux-kit`) | The Shadcn kit of the Toolkit (`--kit=shadcn`) |
| `ThemeSwitcher` and the other PHIL recipes | The PHIL UX kit | `phil/ux-kit-shadcn` (`--kit=github.com/parler-haut-interagir-librement/ux-kit-shadcn`) |
| The forms | The form theme of `phil/ux-bundle` | `tailwind_2_layout.html.twig` of Symfony, or a theme of the site |
| The layout classes | `isStack`, `isCluster`, `isGrid`… | Tailwind utilities |

The administration keeps its theme: `phil/ux-easyadmin-bundle` draws EasyAdmin in PHIL UX from the tokens of
`phil/ux-css`, which stays installed as its dependency and is never served to the pages of the site.

## 1. Remove PHIL UX CSS

```sh
composer remove phil/ux-css phil/ux-kit phil/ux-bundle
```

Then remove what pointed to them:

- `config/packages/phil_ux_kit.yaml`;
- the line `vendor/phil/ux-css/dist: phil-ux` of [asset_mapper.yaml](../config/packages/asset_mapper.yaml);
- the `<link>` to `asset('phil-ux/phil-ux.css')` in [base.html.twig](../templates/base.html.twig).

The recipe `Image` of `phil/ux-kit-shadcn` reads the hosts of the images a page may show from the bundle of
`phil/ux-kit`: a site that installs it keeps `phil/ux-kit` and `config/packages/phil_ux_kit.yaml`.

## 2. Add Tailwind and the toolkit

```sh
composer require symfonycasts/tailwind-bundle:^1.0 twig/html-extra:^3.31 tales-from-a-dev/twig-tailwind-extra:^1.5
composer require --dev symfony/ux-toolkit:^3.8
bin/console tailwind:init
bin/console importmap:require tw-animate-css/dist/tw-animate.css shadcn/dist/tailwind.css
```

`tailwind:init` writes `config/packages/symfonycasts_tailwind.yaml`, with the version of the Tailwind binary, and puts
`@import "tailwindcss";` at the top of `assets/styles/app.css`. The binary is downloaded into `var/tailwind/` on the
first build. `twig/html-extra` gives the recipes `html_cva()`, and `tales-from-a-dev/twig-tailwind-extra` their
`tailwind_classes` filter, which merges a `class` given to a component with its own.

## 3. Write the stylesheet

`assets/styles/app.css` becomes the input of Tailwind. Replace its content with the stylesheet the installation guide
of the Shadcn kit gives for AssetMapper, `vendor/symfony/ux-toolkit/kits/shadcn/INSTALL.md`: the imports of
`../vendor/tw-animate-css/dist/tw-animate.css` and `../vendor/shadcn/dist/tailwind.css`, the `@theme inline` block,
the light tokens on `:root`, the dark ones, and the base layer. The site's own rules follow them.

The PHIL `ThemeSwitcher` writes the reader's choice as `data-theme="light"` or `data-theme="dark"` on `<html>`, and
removes it for the system's choice, where the Shadcn kit reads a `dark` class. Two changes in the stylesheet follow
the switcher:

```css
/* in place of @custom-variant dark (&:is(.dark *)); */
@custom-variant dark (&:where([data-theme=dark], [data-theme=dark] *));

/* in place of the selector .dark of the dark tokens */
[data-theme=dark] {
    /* the dark tokens of the guide */
}

/* the system's choice, when the reader chose neither */
@media (prefers-color-scheme: dark) {
    :root:not([data-theme=light]) {
        /* the same dark tokens */
    }
}
```

The stylesheet stays linked from the layout, never imported from [app.js](../assets/app.js): AssetMapper would turn an
imported stylesheet into a `data:` module of the import map, which the Content Security Policy refuses.

## 4. Install the recipes

```sh
bin/console ux:install badge --kit=shadcn --force --no-interaction
```

```sh
bin/console ux:install button --kit=shadcn --force --no-interaction
```

```sh
bin/console ux:install card --kit=shadcn --force --no-interaction
```

```sh
bin/console ux:install table --kit=shadcn --force --no-interaction
```

```sh
bin/console ux:install theme-switcher --kit=github.com/parler-haut-interagir-librement/ux-kit-shadcn --force --no-interaction
```

`--force` replaces the PHIL UX copies of `templates/components/`; the controller `theme_switcher_controller.js` of
`assets/controllers/` is replaced too. Every other recipe comes the same way: from the Shadcn kit when it holds the
recipe, from `phil/ux-kit-shadcn` for the PHIL ones (`Logo`, `Image`, `Celebration`, `LoginForm`…), whose
[installation guide](https://github.com/parler-haut-interagir-librement/ux-kit-shadcn/blob/main/doc/installation.md)
lists them.

## 5. Adapt the templates

The components keep their names and their props, so `<twig:Button>`, `<twig:Card>`, `<twig:Badge>`, `<twig:Table>`
and `<twig:ThemeSwitcher>` stay as they are, with two differences of the Shadcn kit:

- `Card:Title` is a `div` and has no `level`: put the heading inside it,
  `<twig:Card:Title><h2>…</h2></twig:Card:Title>`, where the PHIL UX kit wrote `<twig:Card:Title :level="2">`;
- `Button` has no `loading`: mark a busy button with `aria-busy="true"` and `disabled`.

In [base.html.twig](../templates/base.html.twig), remove the link to phil-ux: the built stylesheet is the one linked
after it, `styles/app.css`:

```twig
<twig:ThemeSwitcher:Script nonce="{{ csp_nonce('script') }}" />
<link rel="stylesheet" href="{{ asset('styles/app.css') }}">
```

Then replace the classes of phil-ux in the templates with utilities:

| phil-ux | Tailwind |
|---|---|
| `isSkipLink` | `sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:bg-background focus:p-2` |
| `isCluster` | `flex flex-wrap items-center gap-4` |
| `isStack` | `flex flex-col gap-6`; on `<main>`, `mx-auto max-w-5xl px-4 py-6` besides |
| `isGrid` | `grid gap-4 sm:grid-cols-2 lg:grid-cols-4` |
| `isLogo` | `inline-flex items-center gap-2 font-semibold no-underline` |
| `isAlert` | `rounded-lg border p-4`, or the `Alert` recipe of the Shadcn kit |
| `isMuted` | `text-sm text-muted-foreground` |

`grep -rn 'class="is\|class="has' templates` lists what is left.

## 6. The forms

The forms lose the theme of `phil/ux-bundle` with the package. Give them the Tailwind theme of Symfony in
[twig.yaml](../config/packages/twig.yaml), or a theme of the site's own:

```yaml
twig:
    form_themes: ['tailwind_2_layout.html.twig']
```

## 7. Build and check

```sh
bin/console tailwind:build            # once
bin/console tailwind:build --watch    # while the templates change
```

With the Symfony binary, `.symfony.local.yaml` can run the watcher next to the server, as the recipe of
`symfonycasts/tailwind-bundle` suggests. In production, build before compiling the assets:

```sh
bin/console tailwind:build --minify
bin/console asset-map:compile
```

Then run `castor test` and `castor lint`. The tests that read the markup of PHIL UX — a class of phil-ux, the `h3` of
a card — follow the new templates; the accessibility test keeps checking every page, whatever its classes.
