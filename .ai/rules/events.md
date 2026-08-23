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
