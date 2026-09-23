---
paths:
  - 'frontend/src/**'
---

# Src

## Colour is Preline's semantic tokens, never a Tailwind palette class
The SPA is skinned with Preline UI 5 (free npm package). Nothing outside `src/style.css` and `src/styles/themes/battlezones.css` may name a colour: no `bg-neutral-800`, no `text-gray-400`, no raw oklch.

Use Preline's semantic tokens — `bg-background-2` (page), `bg-card` / `border-card-line` / `divide-card-divider` (panels), `bg-navbar` (chrome), `text-foreground`, `text-muted-foreground-1` (secondary), `text-muted-foreground` (tertiary), `bg-primary` / `text-primary-foreground`, `text-destructive`, `bg-surface`. Preline markup copied from the docs is light-first with `dark:` overrides — strip those and use the token, because the app is dark-only (`<html class="dark" data-theme="theme-battlezones">`).

Domain colour that Preline has no token for (`loyalist`, `traitor`, `success`, `text-hall`) is declared in `style.css` and only there. The pre-Preline names — `text-ink*`, `bg-surface-raised`, `bg-accent`, `text-danger` — are gone; do not reintroduce them.

## Icons come from Lucide, never hand-rolled SVG
Every icon is a component from `lucide-vue-next` (e.g. `ChevronLeft`, `CircleUser`), sized with `size-4`/`size-5` and `shrink-0`. Lucide already sets `viewBox`, `fill="none"`, `stroke="currentColor"` and `aria-hidden="true"`, so do not repeat them — and because `aria-hidden` is set, an icon standing alone needs an `sr-only` span beside it or it says nothing at all. Do not paste raw `<svg><path d="…"/></svg>` into a template — the hand-typed paths that used to live in `AppTabBar` and the six back/forward chevrons are gone and should not come back. Colour the icon with a Preline token class on the component, never a Tailwind palette class.

Import the current name, not the deprecated alias Lucide keeps for compatibility: `House` and `CircleUser`, not `Home` and `UserCircle`. The set is stroke-only, so there is no filled variant to reach for — weight and colour carry the emphasis instead. Heroicons was here first and is gone; do not reintroduce `@heroicons/vue` alongside this.
