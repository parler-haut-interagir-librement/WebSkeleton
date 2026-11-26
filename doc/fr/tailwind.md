<!-- phil-dev source-sha256=d8170fb11caef99448f0ff26abee985ea235b24abb246946ab985bb4891412de -->
# Tailwind à la place de PHIL UX CSS

WebSkeleton est habillé par phil-ux, la feuille de style de `phil/ux-css`, et les recettes du kit PHIL UX. Un site qui
préfère [Tailwind CSS](https://tailwindcss.com) passe au kit Shadcn du
[Symfony UX Toolkit](https://symfony.com/bundles/ux-toolkit/current/index.html) et à
[`phil/ux-kit-shadcn`](https://github.com/parler-haut-interagir-librement/ux-kit-shadcn), les recettes PHIL UX écrites en
Tailwind sous les mêmes noms et les mêmes propriétés. Tailwind est construit par
[`symfonycasts/tailwind-bundle`](https://symfony.com/bundles/TailwindBundle/current/index.html) sur AssetMapper : toujours
pas de Node.js, pas de Webpack.

Les étapes ci-dessous se font une seule fois, sur un nouveau site, avant l'écriture de ses propres pages. PHIL UX reste la
valeur par défaut du gabarit.

## Ce qui change

| | PHIL UX (par défaut) | Tailwind |
|---|---|---|
| La feuille de style | phil-ux, servie depuis `vendor/phil/ux-css/dist` sous le nom `phil-ux/phil-ux.css` | `assets/styles/app.css`, construite par le binaire Tailwind de `symfonycasts/tailwind-bundle` |
| `Badge`, `Button`, `Card`, `Table` | Le kit PHIL UX (`phil/ux-kit`) | Le kit Shadcn du Toolkit (`--kit=shadcn`) |
| `ThemeSwitcher` et les autres recettes PHIL | Le kit PHIL UX | `phil/ux-kit-shadcn` (`--kit=github.com/parler-haut-interagir-librement/ux-kit-shadcn`) |
| Les formulaires | Le thème de formulaire de `phil/ux-bundle` | `tailwind_2_layout.html.twig` de Symfony, ou un thème du site |
| Les classes de mise en page | `isStack`, `isCluster`, `isGrid`… | Les utilitaires Tailwind |

L'administration garde son thème : `phil/ux-easyadmin-bundle` dessine EasyAdmin en PHIL UX à partir des jetons de
`phil/ux-css`, qui reste installé comme sa dépendance et n'est jamais servi aux pages du site.

## 1. Retirer PHIL UX CSS

```sh
composer remove phil/ux-css phil/ux-kit phil/ux-bundle
```

Retirez ensuite ce qui y menait :

- `config/packages/phil_ux_kit.yaml` ;
- la ligne `vendor/phil/ux-css/dist: phil-ux` d'[asset_mapper.yaml](../../config/packages/asset_mapper.yaml) ;
- le `<link>` vers `asset('phil-ux/phil-ux.css')` dans [base.html.twig](../../templates/base.html.twig).

La recette `Image` de `phil/ux-kit-shadcn` lit les hôtes des images qu'une page peut montrer dans le bundle de
`phil/ux-kit` : un site qui l'installe garde `phil/ux-kit` et `config/packages/phil_ux_kit.yaml`.

## 2. Ajouter Tailwind et le toolkit

```sh
composer require symfonycasts/tailwind-bundle:^1.0 twig/html-extra:^3.31 tales-from-a-dev/twig-tailwind-extra:^1.5
composer require --dev symfony/ux-toolkit:^3.8
bin/console tailwind:init
bin/console importmap:require tw-animate-css/dist/tw-animate.css shadcn/dist/tailwind.css
```

`tailwind:init` écrit `config/packages/symfonycasts_tailwind.yaml`, avec la version du binaire Tailwind, et met
`@import "tailwindcss";` en haut de `assets/styles/app.css`. Le binaire est téléchargé dans `var/tailwind/` à la première
construction. `twig/html-extra` donne aux recettes `html_cva()`, et `tales-from-a-dev/twig-tailwind-extra` leur filtre
`tailwind_classes`, qui fusionne une `class` donnée à un composant avec la sienne.

## 3. Écrire la feuille de style

`assets/styles/app.css` devient l'entrée de Tailwind. Remplacez son contenu par la feuille de style que donne le guide
d'installation du kit Shadcn pour AssetMapper, `vendor/symfony/ux-toolkit/kits/shadcn/INSTALL.md` : les imports de
`../vendor/tw-animate-css/dist/tw-animate.css` et de `../vendor/shadcn/dist/tailwind.css`, le bloc `@theme inline`, les
jetons clairs sur `:root`, les jetons sombres, et la couche de base. Les règles propres au site les suivent.

Le `ThemeSwitcher` de PHIL écrit le choix du lecteur comme `data-theme="light"` ou `data-theme="dark"` sur `<html>`, et
le retire pour le choix du système, là où le kit Shadcn lit une classe `dark`. Deux changements dans la feuille de style
suivent le sélecteur :

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

Dans [app.js](../../assets/app.js), retirez `import './styles/app.css';` : la feuille de style est plutôt liée depuis la
mise en page, pour qu'elle s'applique avant le premier affichage.

## 4. Installer les recettes

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

`--force` remplace les copies PHIL UX de `templates/components/` ; le contrôleur `theme_switcher_controller.js` de
`assets/controllers/` est remplacé lui aussi. Toute autre recette vient de la même façon : du kit Shadcn quand il contient
la recette, de `phil/ux-kit-shadcn` pour celles de PHIL (`Logo`, `Image`, `Celebration`, `LoginForm`…), dont le
[guide d'installation](https://github.com/parler-haut-interagir-librement/ux-kit-shadcn/blob/main/doc/installation.md)
donne la liste.

## 5. Adapter les gabarits

Les composants gardent leurs noms et leurs propriétés : `<twig:Button>`, `<twig:Card>`, `<twig:Badge>`, `<twig:Table>` et
`<twig:ThemeSwitcher>` restent donc tels quels, avec deux différences du kit Shadcn :

- `Card:Title` est un `div` et n'a pas de `level` : mettez le titre à l'intérieur,
  `<twig:Card:Title><h2>…</h2></twig:Card:Title>`, là où le kit PHIL UX écrivait `<twig:Card:Title :level="2">` ;
- `Button` n'a pas de `loading` : marquez un bouton occupé avec `aria-busy="true"` et `disabled`.

Dans [base.html.twig](../../templates/base.html.twig), liez la feuille de style construite là où se trouvait phil-ux :

```twig
<twig:ThemeSwitcher:Script nonce="{{ csp_nonce('script') }}" />
<link rel="stylesheet" href="{{ asset('styles/app.css') }}">
```

Remplacez ensuite les classes de phil-ux dans les gabarits par des utilitaires :

| phil-ux | Tailwind |
|---|---|
| `isSkipLink` | `sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:bg-background focus:p-2` |
| `isCluster` | `flex flex-wrap items-center gap-4` |
| `isStack` | `flex flex-col gap-6` ; sur `<main>`, `mx-auto max-w-5xl px-4 py-6` en plus |
| `isGrid` | `grid gap-4 sm:grid-cols-2 lg:grid-cols-4` |
| `isLogo` | `inline-flex items-center gap-2 font-semibold no-underline` |
| `isAlert` | `rounded-lg border p-4`, ou la recette `Alert` du kit Shadcn |
| `isMuted` | `text-sm text-muted-foreground` |

`grep -rn 'class="is\|class="has' templates` liste ce qui reste.

## 6. Les formulaires

Les formulaires perdent le thème de `phil/ux-bundle` avec le paquet. Donnez-leur le thème Tailwind de Symfony dans
[twig.yaml](../../config/packages/twig.yaml), ou un thème propre au site :

```yaml
twig:
    form_themes: ['tailwind_2_layout.html.twig']
```

## 7. Construire et vérifier

```sh
bin/console tailwind:build            # once
bin/console tailwind:build --watch    # while the templates change
```

Avec le binaire Symfony, `.symfony.local.yaml` peut lancer l'observateur à côté du serveur, comme le suggère la recette de
`symfonycasts/tailwind-bundle`. En production, construisez avant de compiler les assets :

```sh
bin/console tailwind:build --minify
bin/console asset-map:compile
```

Lancez ensuite `castor test` et `castor lint`. Les tests qui lisent le balisage de PHIL UX — une classe de phil-ux, le
`h3` d'une carte — suivent les nouveaux gabarits ; le test d'accessibilité continue de vérifier chaque page, quelles que
soient ses classes.
