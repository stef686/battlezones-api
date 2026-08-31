---
paths:
  - 'app/Http/Controllers/Events/**'
---

# Events

## A Game result is claimed once, by conditional update
First submission wins. StoreGameResultController claims the Game with `Game::whereKey(...)->whereNull('submitted_at')->update(...)` and 409s when zero rows change — that conditional update is the entire race guard, so never replace it with a read-then-write or a `$game->update()`. There is deliberately no self-correction path: a wrong score is fixed by flagging it for an Organiser, not by resubmitting.

## Self-service endpoints address the current user, never a {user} id
An unclaimed User refuses route binding (see the app rule), so a route like `attendees/{attendee}/members/{member}/faction` 404s for exactly the people who most need it — Players who entered on an Invite and have not set a password.

Anything a Player records about themselves gets a "mine" route resolved from `$request->user()`: `events/{event:slug}/my-game`, `events/{event:slug}/my-faction`. There is one membership per User per Event, so no id is needed. Organisers correcting someone else's row do it in Filament.

## A Player who has not claimed their account cannot be named in a URL
`User::resolveRouteBinding()` returns null for an unclaimed account, so any route with a `{member}` User parameter 404s for exactly the Player an invited partner is. Endpoints that act on a waiting team mate are therefore keyed on the membership instead: `PATCH .../attendees/{attendee}/members/{membership}` (amend name, email, faction) and `POST .../members/{membership}/invite` (resend). `PATCH /my-faction` solves the same problem the other way, by addressing the Player as "mine".

Both refuse once `$membership->user->isClaimed()`: a claimed account's name and address are its owner's alone. The membership id reaches the SPA as `membership_id` on the detail resource's members, alongside `invite_outstanding`, and both are gated on `Gate::allows('update', $attendee)` — who has answered their invitation is the party's business, not the field's.

`DELETE .../attendees/{attendee}/members/{membership}` is keyed the same way and for the same reason — the Player most likely to be dropped is the one who never answered.

Still outstanding: `POST .../members/{member}/army-list/unlock` binds a User, so an Organiser cannot reopen the list of a Player who has not claimed their account.
