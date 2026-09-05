---
paths:
  - 'app/Http/Requests/Events/**'
---

# Requests Events

## Editing an Event refuses fields loudly, and never moves its Schedule
`PATCH /events/{event:slug}` (`UpdateEventRequest`) accepts name, description, the four venue fields, starts_at, ends_at, registration_closes_at and max_attendees. `slug`, `status` and `pairing_format` carry a `prohibited` rule so a caller that tries is told, rather than watching the change silently not happen. Do not extend the accepted list without re-reading `docs/adr/0002-general-event-patch-endpoint.md` — the refuse list is the decision.

`game_system_id` and `attendee_size` are refused *conditionally*: free while the Event has no Attendees, `prohibited` the moment one exists, with a message naming the reason. The gate is `attendees()->exists()` alone, whatever the Event's status — before the first entry nothing has been built at the old party size and no Faction has been chosen against the old Game System, so neither field can strand anything. Team size is validated 1–8. See the amendment in ADR 0002.

Two behaviours are deliberate: `max_attendees` below the entered count is a validation failure, not an accepted over-fill; and changing the Event's dates leaves every Schedule block's absolute timestamps alone, because a venue change usually affects one day and shifting them all would corrupt a schedule that was right.

An Event that is not publicly visible answers 404 to anyone but its Organisers, from `prepareForValidation` — a 403 would confirm it exists.

## An Event's Score Types are written as one ordered set
`PUT /events/{event:slug}/score-types` (`ReplaceEventScoreTypesRequest` → `ReplaceEventScoreTypes`) takes the complete ordered set, never a row at a time. Position sets `display_order`, and position among the rows with `counts_for_ranking` sets `ranking_order`; a row that does not count for ranking has none. Slugs are never accepted from the client — a result is submitted and the Standings sorted by slug, so a rename keeps the slug it had.

A row arriving without an id is created, with a slug derived from its name and made unique within the Event; a column the payload leaves out is deleted.

Refusals: an empty set; two rows claiming `is_primary`; a derived row without win, draw and loss points; and leaving out a column **Games have already been scored on**. That last one is the guard that matters — `game_scores` cascades on delete, so without it an Organiser tidying a column away would take the Event's whole score history, and the Standings computed from it, with them. See `docs/adr/0005-score-types-are-replaced-as-one-ordered-set.md` — reordering is retroactive, because it re-ranks the Standings and therefore the next Round's pairings.
