---
paths:
  - 'app/Http/Requests/Events/**'
---

# Requests Events

## Editing an Event refuses fields loudly, and never moves its Schedule
`PATCH /events/{event:slug}` (`UpdateEventRequest`) accepts name, description, the four venue fields, starts_at, ends_at, registration_closes_at, max_attendees, colour and (until the first Schedule block) timezone. `slug`, `status` and `pairing_format` carry a `prohibited` rule so a caller that tries is told, rather than watching the change silently not happen. Do not extend the accepted list without re-reading `docs/adr/0002-general-event-patch-endpoint.md` — the refuse list is the decision.

`game_system_id` and `attendee_size` are refused *conditionally*: free while the Event has no Attendees, `prohibited` the moment one exists, with a message naming the reason. The gate is `attendees()->exists()` alone, whatever the Event's status — before the first entry nothing has been built at the old party size and no Faction has been chosen against the old Game System, so neither field can strand anything. Team size is validated 1–8. See the amendment in ADR 0002.

`timezone` is refused the same way, on a different gate: free while `Event::hasSchedule()` is false, `prohibited` once a Schedule block exists, because every block's day is read in the zone. The single-Event response (anything built `withViewer()`) carries `timezone_is_fixed` so Settings can show the zone locked before an Organiser tries. Create and PATCH both validate `timezone:all`, and `GET /timezones` (`ListTimezonesController`) serves exactly `DateTimeZone::listIdentifiers()` for the SPA's `TimezoneField`. Never widen this to `all_with_bc` or list the browser's `Intl` zones instead: which old names the server knows depends on its tzdata packaging (CI's lacks them), so the two lists would disagree by environment. The wizard maps the browser's own zone through `currentZoneName` in `lib/timezones.ts` before defaulting to it.

Two behaviours are deliberate: `max_attendees` below the entered count is a validation failure, not an accepted over-fill; and changing the Event's dates leaves every Schedule block's absolute timestamps alone, because a venue change usually affects one day and shifting them all would corrupt a schedule that was right.

An Event that is not publicly visible answers 404 to anyone but its Organisers, from `prepareForValidation` — a 403 would confirm it exists.

## An Event's Score Types are written as one ordered set
`PUT /events/{event:slug}/score-types` (`ReplaceEventScoreTypesRequest` → `ReplaceEventScoreTypes`) takes the complete ordered set, never a row at a time. Position sets `display_order`, and position among the rows with `counts_for_ranking` sets `ranking_order`; a row that does not count for ranking has none. Slugs are never accepted from the client — a result is submitted and the Standings sorted by slug, so a rename keeps the slug it had.

A row arriving without an id is created, with a slug derived from its name and made unique within the Event; a column the payload leaves out is deleted.

Refusals: an empty set; two rows claiming `is_primary`; a derived row without win, draw and loss points; and leaving out a column **Games have already been scored on**. That last one is the guard that matters — `game_scores` cascades on delete, so without it an Organiser tidying a column away would take the Event's whole score history, and the Standings computed from it, with them. See `docs/adr/0005-score-types-are-replaced-as-one-ordered-set.md` — reordering is retroactive, because it re-ranks the Standings and therefore the next Round's pairings.

## An Event slug is held to one shape, on the create, the availability check and the SPA
`ValidatesEventSlug` owns the slug's shape (3–60 of `[a-z0-9]` with single hyphens between words), the reserved list (`RESERVED_SLUGS`: `new`, `create`) and their messages. `StoreEventRequest` adds `unique`; `ShowEventSlugAvailabilityRequest` (`GET /api/event-slug-availability?slug=`, claimed accounts only via `EventPolicy::create`, throttled per account by `throttle:slug-check`) refuses a malformed or reserved slug with 422 and otherwise answers only `available`, Drafts included, and nothing else about the holding Event (ADR 0006). The SPA mirrors the shape and reserved list in `slugProblem` in `lib/event-wizard.ts` and asks the endpoint only about slugs that pass it. Change all three together. The endpoint sits outside `events/` on purpose: any word there is a slug an Event could hold.

## An Event is created in a built pairing format, with only the settings the wizard asks for
`StoreEventRequest` requires `pairing_format` and accepts only `PairingFormat::available()` — Swiss today. An unbuilt format is refused with 422 rather than accepted and paired as Swiss (ADR 0006); flip `isAvailable()` as each lands, and the SPA's `PAIRING_FORMATS` in `lib/event-fields.ts` with it. `settings` is optional and limited to `round_count` (null for open-ended, else a whole number from 1 to `StoreEventRequest::MAX_ROUNDS`, 20, mirrored as `MAX_ROUNDS` in `lib/event-wizard.ts`); `settings` itself may be left out or null and `requires_opposed_allegiance`; anything else in it, such as `standings_visible`, is refused, because `EventSettings::fromArray` throws on an unknown key. `eventAttributes()` hands `CreateEvent` the settings as an `EventSettings`, which is the only shape the cast will write.
