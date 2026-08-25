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

**Refused, deliberately:**

- `slug` — the Event's public identity. It is in every Invite email, every shared link and every
  SPA route. Changing it silently breaks credentials already in people's inboxes.
- `attendee_size` — every existing registration was built at the current party size. Changing it
  leaves Attendees that are the wrong shape for their own Event.
- `status` and `pairing_format` — these drive Round generation and visibility, not presentation.
  They need their own transitions with their own rules, not a field in a settings form.

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
