---
paths:
  - 'frontend/src/router/**'
---

# Router

## Unclaimed restricted mode lives only in the router guard
An unclaimed account (entered on an Invite, no password yet) is confined to its own Event plus the Claim flow. That rule is enforced once, in `router.beforeEach`, and nowhere else — a screen that re-checks is a screen that can forget to.

Routes opt out with `meta.unclaimed: true` (login, invite, claim, forgot/reset password). Confinement compares `to.params.eventSlug` against the Event slug remembered with the Invite, so a route with no `eventSlug` is a public surface and is not theirs.

The guard awaits `session.load()` when the viewer is unknown: on a cold page load the profile has not arrived yet, and an account not known to be unclaimed is not confined at all. Unknown deliberately fails open — the API enforces authorization; this is only about where an unclaimed Player is shown.

## Only token screens opt out of chrome; sign-in keeps the tab bar
`meta.chrome: false` is for screens reached with a credential in the URL (reset-password, invite, feedback, claim). Login and forgot-password stay inside the shell: the guard sends every signed-out reader of a guarded route to /login, and the avatar slot leads there, so a chrome-less login strands a guest with no way back to the public Event screens.
