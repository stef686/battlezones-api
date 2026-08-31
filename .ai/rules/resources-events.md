---
paths:
  - 'app/Http/Resources/Events/**'
---

# Resources Events

## One Score Type leads a Game listing, and the API decides which
An Event can be scored on any number of columns; a listing of Games has room for one number per team. `EventScoreType.is_primary` marks which, and `Event::primaryScoreType()` resolves it: the marked column, else the first non-derived one by display order, else the first. A derived column (Match Points, worked out from the result) loses to the score actually played for.

Resources send `is_primary` per column already resolved, so exactly one column carries `true` however the Event was set up. Do not send the raw flag and leave the client to fall back — the SPA filters on it (`listedColumns` in `frontend/src/api/rounds.ts`) and a Round with none marked would list no scores at all.
