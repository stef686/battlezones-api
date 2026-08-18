---
paths:
  - 'app/**'
---

# App

## Unclaimed accounts stay off public surfaces
A User with a null `claimed_at` exists only because someone else invited them. `User::resolveRouteBinding()` returns null for those, so any route binding a `{user}` 404s automatically — do not re-check by hand. Listing and search queries bind no model, so those must add the `claimed()` scope themselves (see SearchUsersController, ListFollowersController).

Setting a password is what claiming means, whichever route it arrives by: run `App\Actions\Users\ClaimAccount` rather than writing `password` directly, so `claimed_at` is set and outstanding invite tokens are revoked in one place.
