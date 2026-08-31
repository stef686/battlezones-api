---
paths:
  - app/Models/Event.php
  - app/Models/EventScheduleBlock.php
---

# Models

## Several Score Types may be flagged primary; the first wins
`event_score_types.is_primary` is deliberately unconstrained — no unique index, no Filament save hook clearing the others. `Event::primaryScoreType()` takes the first flagged one in display order, and the Filament toggle says so. Do not "fix" this with a partial unique index or a validation rule.

Resources always send `is_primary` already resolved, so exactly one column carries `true` on the wire however many carry it in the table.

## Live is a latch, so "now" is derived from the Event, never from the Round
`RoundStatus` has two cases, Draft and Live, and nothing ever takes a Round back out of Live — that is why `Event::currentRound()` is "the highest-numbered live Round" rather than a pointer. Anything asking "is this Round happening now?" must ask the Event the same way.

`EventScheduleBlock::targetState()` returns a `ScheduleTargetState` (or null): a Round is `Live` only while it is the Event's current one, `Finished` once the field has moved past it or the Event is Completed, and null while it is a Draft. A painting block is `Live` while its Poll is open and `Finished` once it has closed. Asking `$round->isLive()` per block, which is what this used to do, badges every published Round as happening now — Round 1 included, right through to the prizegiving.

There is deliberately no stored "finished" status. It would be a second copy of a fact the Event already holds, needing a transition somebody performs and undoes when a Round is withdrawn, and a third `RoundStatus` case would ripple into every `isLive()`/`scopeLive()` check — pairing, standings, the Allegiance freeze, the pulse.

`Event::currentRound()` is memoised per instance, like `openPoll()`, because a schedule asks it once per Round block.
