---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Public endpoints must read the viewer from the sanctum guard
Public read endpoints (events, rounds, games) carry no auth middleware, so `$request->user()` resolves the `web` guard and is null for token clients. Use `$request->user('sanctum')` when a public endpoint needs to know who is looking — e.g. showing Draft Rounds to Organisers. `Event::isOrganisedBy()` accepts null for exactly this. `actingAs()` in tests still works, because Sanctum's guard falls back to the web guard when there is no bearer token.
