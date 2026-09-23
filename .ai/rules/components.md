---
paths:
  - 'frontend/src/components/**'
---

# Components

## Reuse the shared Preline components rather than retyping their classes
Preline's class strings are a dozen utilities long, which is how a codebase grows four slightly different primary buttons. Before writing markup, reach for these:

- `AppButton` — every button and every link-styled-as-a-button. Variants `primary|secondary|ghost|danger`, sizes `sm|md`, plus `block`. Passing `to` renders a real RouterLink anchor instead of a `<button>`, so it stays middle-clickable.
- `AppAlert` — short outcomes. `tone="error"` announces with `role="alert"`; `success` and `info` stay polite with `role="status"`.
- `AuthCard` — the centred card for every screen reached without a session (login, claim, reset, forgot, invite). `title`, optional `subtitle`, default slot, optional `#footer`.
- `TextField` / `SelectField` — labelled inputs. They own `useId()` label association and wire hint + errors through `aria-describedby`; do not hand-roll a labelled input. Where a single field's placeholder already says what it is — the Round screen's team search — pass `label-hidden` and a `placeholder` rather than dropping the label: it goes `sr-only`, never out of the markup, because an input with no accessible name says nothing to a screen reader and a placeholder is not a label.

- `TabStrip` — a row of tabs and the panel under it, used by the Game screen (the two sides of the table) and the Attendee screen (the Players in a team). It owns the roles, the arrow keys and the single tab stop a tablist owes a reader, and takes `items` (anything with `id` and `name`), an `aria-label`, and an `id-prefix` that names every tab and panel testid. Do not hand-roll a second tablist; two implementations means one of them is quietly broken for the keyboard.

A raw `<button>` is right only for a selection toggle that carries `aria-pressed` and its own selected styling (rating pickers, poll picks, pairing swap). Chrome (the tab bar) is `AppShell` + `AppTabBar` and is applied in `App.vue` from `meta.chrome`, never by a view.

## Two navs, and only the fixed-slot one keeps a constant shape
Chrome is two bars with different jobs. `AppTabBar` is the global bottom bar — Home, Events, Messages, avatar — app-level destinations, fixed four slots, drawn as icons alone. Each slot keeps its label as an `sr-only` span: an icon with no accessible name says nothing to a screen reader, and the account slot's name is the viewer's own, which no icon can carry. Its "destinations are the same for everybody" rule applies to it and only it: dropping a slot there reflows the others under a Player's thumb mid-round, so a tap lands on the wrong thing. Home/Events/Messages ship visibly inert (`aria-disabled`, not focusable) until those screens exist; the avatar slot is live from day one and carries sign-in, because no other chrome routes to `/login`.

The Event nav is the pinned horizontal scroller under the Event header — Home, Rounds, Standings, Attendees, Schedule, My team, Organisers. Home leads, and points at the Event screen itself. Its tabs are variable-width, so it MAY omit a trailing tab that would dead-end: My team appears only when `viewer.is_attendee`, and Organisers only when `viewer.permissions.organise`. Both are absent for most readers, which is why they trail the five that never are — a missing trailing tab moves nothing in front of it. This is a deliberate narrowing of the tab bar's rule, not a violation of it. Do not make the tab bar viewer-dependent, and do not make the Event nav rigid.

Organisers is last, and is the only tab that leaves the Player-facing Event for the organiser's side of it. It replaced a "Run the event" call-to-action on the Event screen: an Organiser mid-round is on Rounds or Standings, not on Home, and routing them through Home to reach their own tools was a tap for nothing. Like every viewer-dependent tab it is offered on the permission the API sent and never hidden with CSS.

## The Event header owns the safe-area inset, and its overlay has one colour rule
`EventHeader` is drawn by `AppShell` on every event-scoped route (keyed off the route's event slug, exactly as the tab bar is) and nowhere else. It renders nothing until the Event is read, so an Event nobody may see does not get a blank header above its "not found".

The safe-area inset is on the header, not on `AppShell`'s wrapper: the surface runs under the status bar and only the type clears it, which makes the element taller than the 160px of visible content on a notched device.

Overlay text sits on two scrims (`event-header-scrim-top` / `event-header-scrim-bottom`, both declared in `style.css`) that are present whether or not a Banner is behind them, so `text-event-header-foreground(-muted)` is the single colour rule in every state. Do not swap the overlay's colour based on whether there is an image, and do not pin the header — it scrolls away with the page; only the Event nav pins.

## The Event nav lights a tab from a route-name map, never from the URL
`EventNav` decides its active tab from `chipOfRoute`, a route-name → tab map kept beside the tab list. The active tab is marked with a bottom border, not a filled pill — pills read as buttons and compete with the screen's own calls to action. A Round's detail screen lights Rounds and an Attendee's lights Attendees, the Event screen lights Home, and the organiser screens beneath it (settings, flags) light Organisers; Poll and My game light nothing. Do not swap this for RouterLink's inclusive active matching — that lights Rounds on every screen under the Event path.

The lit tab is scrolled into view on mount and on every route change, because the tabs overflow a phone and a deep link would otherwise land on a nav with nothing visibly selected.

The strip scrolls natively (`overflow-x-auto`, no scroll-snap, no drag handler) and its trailing fade is the `event-nav-fade` utility in `style.css`. It hides only when the Event query errors — it stays through the load, since the five fixed sections do not depend on the Event.

## The avatar slot opens AccountDrawer, and the tab bar shows at every width
Signed in, the tab bar's avatar slot is a `<button aria-haspopup="dialog">` drawing `AppAvatar` initials. It opens `AccountDrawer`, the only place to log out. That makes the bar load-bearing on desktop too, so it has no `md:hidden` until desktop chrome replaces it (#143).

`AccountDrawer` is a native `<dialog>` opened with `showModal()`. Do not swap it for Preline HSOverlay: the browser owns the focus trap, Esc and the inert page, and Vue owns only `v-model:open`. The Close button carries `autofocus` so that Log out never receives the opening focus. Its sections are the same for every User: "Player" and "Organiser" are copy, not roles (see CONTEXT.md).

Logging out goes through `useLogout`. `ApiClient.logout()` makes a best-effort revoke with a 3s cap, then forgets the token whatever happens. The composable then clears the session and the TanStack Query cache and calls `router.replace` to login. Do not make logout wait on the API. `TeamAvatar` is now `AppAvatar`, with testids `avatar` and `avatar-placeholder`.
