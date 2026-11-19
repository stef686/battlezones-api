---
paths:
  - 'app/**'
---

# App

## Unclaimed accounts stay off public surfaces
A User with a null `claimed_at` exists only because someone else invited them. `User::resolveRouteBinding()` returns null for those, so any route binding a `{user}` 404s automatically — do not re-check by hand. Listing and search queries bind no model, so those must add the `claimed()` scope themselves (see SearchUsersController, ListFollowersController).

Setting a password is what claiming means, whichever route it arrives by: run `App\Actions\Users\ClaimAccount` rather than writing `password` directly, so `claimed_at` is set and outstanding invite tokens are revoked in one place.

## Countries are the Country enum, never a table
`App\Enums\Country` (ISO 3166-1 alpha-2, with `label()`) is the one list of countries: it casts `venue_country`, `users.country` and `clubs.country`, and validates them via `Rule::enum`. A picker reads it from `GET /api/countries` (ListCountriesController), which serves the cases in declared order — already name order. Do not add a countries table or seeder; it would be a second list that validation never consults.

## Attendees are deleted through DeleteAttendee, Filament included
`App\Actions\Events\DeleteAttendee` holds the never-paired check, the Avatar clean-up after commit, telling the Attendee's Players (`RemovedFromEventNotification`, after commit, actor excluded, Published and Active only), and whatever else a delete must do. It takes the acting User; Filament passes the signed-in admin. Every path that removes an Attendee calls it — the API controller and both Filament deletes in `AttendeesRelationManager` (`->using()`, catching its `ValidationException` as a failure notification). Never call `$attendee->delete()` directly, or a paired Attendee vanishes from its opponents' Games.
