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

## Team-mate endpoints are keyed on the membership, and scoped bindings resolve unclaimed accounts
`PATCH .../attendees/{attendee}/members/{membership}` (amend name, email, faction) and `POST .../members/{membership}/invite` (resend) are keyed on the seat rather than the Player. `DELETE .../members/{member}` and the army-list unlock still take a User, which is fine — nothing about them travels. The seat carries the Faction and the army list, so correcting a mistyped address moves the membership to the new account with both intact, and revokes the credential emailed to the old one. All three refuse once `$membership->user->isClaimed()`: a claimed account's name and address are its owner's alone.

Do not "fix" these to a `{member}` User parameter on the grounds that an unclaimed account is unresolvable. It is not, under a scoped binding: `Route::scopeBindings()` resolves the child through `Model::resolveChildRouteBindingQuery()`, which queries the parent's relation directly and never calls `User::resolveRouteBinding()`. The unclaimed guard there only bites on an unscoped parameter, which is why `PATCH /my-faction` is addressed as "mine" — it hangs off the Event, with no Attendee to scope it to.

The membership id reaches the SPA as `membership_id` on the detail resource's members, with `invite_outstanding` and — only while that invitation is outstanding — the `email` it went to. All three are gated on `Gate::allows('update', $attendee)`: who has answered is the party's business, not the field's, and a claimed account's address is nobody's but its owner's.

## Publishing an Event is its own transition, and its preconditions live in one action
`POST /events/{event:slug}/publish` (PublishEventController → `App\Actions\Events\PublishEvent`) takes a Draft to Published; `status` stays `prohibited` on the Event PATCH (ADR 0002). It 404s for anyone `isVisibleTo()` refuses, then authorises `EventPolicy::publish` — the Lead Organiser, plus admins for Filament. The action refuses a non-Draft under `status`, and otherwise names *every* unmet precondition at once under the field that fixes it (`venue_name`, `venue_city`, `ends_at` already past), so one attempt says everything in the way.

The organise hub's `PublishEventCard` lists the same checks before the button is pressed, from `publishChecklist` in `frontend/src/lib/publishing.ts`. That is a mirror, not the rule: add a precondition to the action and the checklist together. `viewer.permissions.publish` is sent on the single Event only.

## A Draft takes nobody in, and every way in asks the Event
Nobody enters, is added to, or is invited into a Draft. `Event::ensureIsNotDraft()` throws `EventIsDraft`, which renders itself as 422 "Publish the event first." It is called from `RegisterAttendee` and `SendEventInvite` (so registering, inviting a Captain, resending and partner invites are all covered) and from StoreAttendeeMemberController. Every one of those routes 404s anyone `Event::isVisibleTo()` refuses before it authorises, so only Organisers ever see the 422. A new way into an Event must do both — do not re-check `status` by hand.

Registering and inviting first 404 anyone `isVisibleTo()` refuses, so only an Organiser ever sees the 422: telling a stranger to publish an Event would confirm it exists.
