---
paths:
  - 'frontend/src/views/**'
---

# Views

## Not-found wording is one component, never written per screen
An Event that is not publicly visible and one that never existed both answer 404, and the SPA must not distinguish them either. Every screen renders `MissingNotice` for `kind === 'not_found'` rather than phrasing its own message.

Nothing in that path may say "private", "hidden", "not published", or "no permission" — the wording lives in one component precisely so a new screen cannot leak the difference by improvising. `errors.ts` keeps the matching API-level message ("That could not be found."), for the same reason.
