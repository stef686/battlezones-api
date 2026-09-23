# One general `PATCH /events/{event:slug}`, with an explicit field policy

Until now an Event could not be edited at all: `routes/api/events.php` had no create or update
route, and every controller in it is single-action. Adding a Banner forced the question, and we
chose a **general** `PATCH /events/{event:slug}` taking a JSON body of Event fields, rather than a
family of single-purpose endpoints, so that an Organiser can fix a wrong name, venue or date
without a new route per field.

The generality is bounded by an explicit field policy, and that policy — not the endpoint — is the
decision worth remembering.

## Field policy

**Accepted:** `name`, `description`, `venue_name`, `venue_address`, `venue_city`, `venue_country`,
`starts_at`, `ends_at`, `registration_closes_at`, `max_attendees`.

**Accepted only while the Event has no Attendees** (amended, see below): `game_system_id`,
`attendee_size`.

**Refused, deliberately:**

- `slug` — the Event's public identity. It is in every Invite email, every shared link and every
  SPA route. Changing it silently breaks credentials already in people's inboxes.
- `status` and `pairing_format` — these drive Round generation and visibility, not presentation.
  They need their own transitions with their own rules, not a field in a settings form.

## Amendment: the shape of an Event is editable until the first entry

`game_system_id` and `attendee_size` were both refused unconditionally. The Event format screen
gives an Organiser the shape of their own Event, so both are now accepted **while no Attendee has
entered**, and carry the same loud `prohibited` refusal the moment one has — each with a message
naming the reason rather than the generic one.

The line is drawn at the first entry, and at nothing else:

- Before it, nothing has been built at the old party size, so there is no Attendee to leave the
  wrong shape for its own Event — the original reason `attendee_size` was refused.
- Before it, no Player has chosen a Faction, and every Faction belongs to a Game System, so
  changing the system cannot strand a choice already made.

The gate is the presence of Attendees alone, **regardless of the Event's status**: a published
Event nobody has entered is as safe to reshape as a draft, and a draft with an Attendee is not.
Status is a poor proxy for the thing that actually matters.

`attendee_size` is validated 1–8 and `game_system_id` must exist. Neither field is migrated when it
changes: the gate exists precisely so that no migration is needed.

The Banner is not in the list either: PHP does not populate `$_FILES` for `PATCH` bodies, so it has
its own multipart route (`POST`/`DELETE /events/{event:slug}/banner`). See ADR 0003.

## Consequences

- **Moving an Event's dates does not move its Schedule blocks.** `EventScheduleBlock` stores
  absolute `starts_at`/`ends_at` timestamps and derives its day from them, by design. If an
  Organiser shifts an Event from 14–15 March to 21–22 March, the blocks stay where they were and
  the Organiser must move them. This is chosen, not overlooked: a venue change often affects only
  one day, and silently shifting every block by the delta would corrupt a schedule that was right.
- `max_attendees` below the current `attendees_count` is rejected by validation rather than
  accepted and allowed to over-fill the Event.
- The endpoint is authorised by the existing `EventPolicy::organise`.
- Do not extend the accepted list without revisiting this file. The refuse list is the point.
