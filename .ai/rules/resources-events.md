---
paths:
  - 'app/Http/Resources/Events/**'
---

# Resources Events

## One Score Type leads a Game listing, and the API decides which
An Event can be scored on any number of columns; a listing of Games has room for one number per team. `EventScoreType.is_primary` marks which, and `Event::primaryScoreType()` resolves it: the marked column, else the first non-derived one by display order, else the first. A derived column (Match Points, worked out from the result) loses to the score actually played for.

Resources send `is_primary` per column already resolved, so exactly one column carries `true` however the Event was set up. Do not send the raw flag and leave the client to fall back — the SPA filters on it (`listedColumns` in `frontend/src/api/rounds.ts`) and a Round with none marked would list no scores at all.

The one exception is `EventScoreTypeResource`, which the Event format screen edits against: it sends the raw `is_primary` on purpose, because the flag an Organiser toggles must be the flag stored. It also sends `is_scored` (from `withExists('scores')`) — whether any Game has been scored under the column, which is what a removal is refused on. See `docs/adr/0005-score-types-are-replaced-as-one-ordered-set.md`.
