# An Event's Score Types are replaced as one ordered set

The Event format screen is the first non-admin writer of Score Types: until now everything that
created one was a seeder, a factory or the Filament relation manager. Giving an Organiser the
scoring of their own Event raised the question of what the write looks like.

## Decision

One endpoint, `PUT /events/{event:slug}/score-types`, taking the **complete ordered set** in one
request and applying it in a transaction. Not a REST resource per row.

### Why one request rather than per-row REST

Order is doing two jobs at once. Position in the array is the display order, and position **among
the rows marked as counting for ranking** is the ranking order. A per-row API would make the order a
separate round trip, so an Organiser who drags a column above another and loses their connection
half way could end up with columns displayed in one order and Standings ranked in another. Nothing
would look wrong; the Standings would just be sorted on tiebreaks nobody chose. Replacing the set in
one transaction makes that impossible.

This is the same shape as `ReplaceBallot`, and for the same reason: where a set has to stay
internally consistent, the set is the unit of writing.

### Why order carries ranking

`ranking_order` was a number an admin typed into Filament, separately from `display_order`. Two
numbers meaning almost the same thing is two numbers that drift apart. An Organiser thinks in one
list — "match points, then victory points as the tiebreak" — so the screen shows one list and the
server derives both numbers from it. A row that does not count for ranking has `ranking_order` null.

The consequence is real and intended: **the ranking is the pairing**. The Score Types that count for
ranking drive the Standings and, through them, who meets whom in Swiss. Reordering columns mid-Event
therefore changes the next Round's pairings as well as the table on screen. Standings are computed
rather than stored, so the change is retroactive across the whole Event the moment it lands.

There is a second re-pointing to be aware of: a derived column is worked out from the **first
non-derived column in display order** (`StoreGameScores`), so reordering can change what Match
Points are computed from for Games scored from then on.

### Why slugs are server-owned

The payload has no `slug`. A Score Type is addressed by slug when a Player submits a result and when
the Standings are sorted, so a slug that moved under a client would refuse a result already in
flight and break a bookmarked sort. A rename keeps the slug; the name is what people read, the slug
is what the API answers to.

## Consequences

- **A row without an id is created; a column left out is deleted** — except one Games have already
  been scored on, which is a validation failure naming it. `game_scores` cascades on delete, so that
  guard is the only thing between an Organiser tidying a column away and the Event's whole score
  history — and the Standings computed from it — going with it. An unscored column is theirs to
  remove. A new column's slug is derived from its name and made unique within the Event; the client
  never supplies one.
- At most one row may claim `is_primary`. The server still resolves a leader when none is marked —
  `Event::primaryScoreType()` — so there is no unique index and no clearing hook. The screen takes
  the lead off whoever held it rather than letting an Organiser trip the refusal.
- A derived column must carry win, draw and loss points; there is nothing to work it out with
  otherwise.
- An empty set is refused: an Event scored on nothing has no Standings and nothing for a Player to
  enter.
- Changing a derived column's points does **not** rewrite Games already scored. No backfill exists
  anywhere in the platform and this does not add one.
- `EventScoreTypeResource` sends the **raw** `is_primary`, unlike the listing resources, which send
  it already resolved (see `.ai/rules/resources-events.md`). This is the editor: the flag an
  Organiser toggles is the flag stored, and resolving it here would show them a decision they had
  not made as one they had.
