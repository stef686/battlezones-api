---
paths:
  - 'frontend/src/composables/**'
---

# Composables

## Live-ness is one pulse poll, never polling the fat resources
`useEventPulse` is the only thing in the SPA that polls. It reads `GET events/{slug}/pulse` (fixed cost, four aggregates) and invalidates only the query whose stamp moved. Never add a `refetchInterval` to Standings, Rounds or my-game — that is a full recompute per Player per tick, which is exactly what the pulse endpoint exists to avoid.

Policy lives in `lib/polling.ts` as pure functions so it is testable without timers: stop when the Event is not `active` or the tab is hidden; back off only on 429 (honouring `Retry-After`), never on a network drop; reset on success.

Query keys come from `api/keys.ts` and nest like the API's resources (`['events', slug, 'rounds', id]`), which is what makes that invalidation precise. Add new keys there rather than inline in a view.
