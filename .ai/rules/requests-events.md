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
