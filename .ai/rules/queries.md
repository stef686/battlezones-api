---
paths:
  - 'app/Queries/**'
---

# Queries

## Standings are computed, never stored
`EventStandingsQuery` aggregates `game_scores` per Attendee and ranks with MySQL `RANK()` over `ranking_order` (Match Points, then Victory Points). There is no `event_standings` table any more — do not reintroduce one or cache the result: every new write path that touches a score (organiser edit, bye entry, flag resolution) would have to remember to recalculate, and the one that forgets leaves Standings wrong mid-event. `?sort_by` reorders the list only; `position` always reports the true rank.
