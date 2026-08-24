---
paths:
  - 'frontend/src/views/**'
---

# Views

## Not-found wording is one component, never written per screen
An Event that is not publicly visible and one that never existed both answer 404, and the SPA must not distinguish them either. Every screen renders `MissingNotice` for `kind === 'not_found'` rather than phrasing its own message.

Nothing in that path may say "private", "hidden", "not published", or "no permission" — the wording lives in one component precisely so a new screen cannot leak the difference by improvising. `errors.ts` keeps the matching API-level message ("That could not be found."), for the same reason.

## A dead feedback link explains itself; MissingNotice is for resources
FeedbackView deliberately does not render `MissingNotice` for its 404. The not-found wording rule exists so a screen cannot leak whether an Event exists but is hidden; a feedback token is a credential the reader was emailed, and "we could not find that page" is useless to somebody holding a spent link.

It still leaks nothing: unknown, already used and expired all answer 404 in the API and are stated together on the screen as one outcome. Do not split them apart — which of the three it is only matters to somebody holding a token they were never sent.
