---
paths:
  - 'app/Queries/**'
---

# Queries

## Standings are computed, never stored
`EventStandingsQuery` aggregates `game_scores` per Attendee and ranks with MySQL `RANK()` over `ranking_order` (Match Points, then Victory Points). There is no `event_standings` table any more — do not reintroduce one or cache the result: every new write path that touches a score (organiser edit, bye entry, flag resolution) would have to remember to recalculate, and the one that forgets leaves Standings wrong mid-event. `?sort_by` reorders the list only; `position` always reports the true rank.

## Position movement is computed with a round cap, not snapshotted
Standings movement ("up 2 since last round") does NOT need a stored snapshot, and must not get one — the no-stored-standings rule covers it. A score belongs to a game and a game to a round, so "where everybody stood after round N" is the same aggregate with `game_scores.game_id` limited to games in rounds numbered <= N. `EventStandingsQuery` joins a second ranked subquery for that and exposes `previous_position`; `Standing::movement()` turns it into places gained.

Two details that are load-bearing: the round cap goes on the join, not a `where`, so an attendee who had played nothing by then still ranks (on zero) instead of dropping out and reading as a climber; and the baseline round is found from `game_scores` existing, never from a round status — `RoundStatus` has no completed state on purpose. Movement is null until two rounds have scores, which draws no arrows rather than a table of dashes.
