---
paths:
  - 'frontend/src/views/**'
---

# Views

## Not-found wording is one component, never written per screen
An Event that is not publicly visible and one that never existed both answer 404, and the SPA must not distinguish them either. Every screen renders `MissingNotice` for `kind === 'not_found'` rather than phrasing its own message.

Nothing in that path may say "private", "hidden", "not published", or "no permission" — the wording lives in one component precisely so a new screen cannot leak the difference by improvising. `errors.ts` keeps the matching API-level message ("That could not be found."), for the same reason.

## A dead feedback link explains itself; MissingNotice is for resources
FeedbackView deliberately does not render `MissingNotice` for its 404. The not-found wording rule exists so a screen cannot leak whether an Event exists but is hidden; a feedback token is a credential the reader was emailed, and "we could not find that page" is useless to somebody holding a spent link.

It still leaks nothing: unknown, already used and expired all answer 404 in the API and are stated together on the screen as one outcome. Do not split them apart — which of the three it is only matters to somebody holding a token they were never sent.

## Back links only where the Event nav cannot reach
The Attendee and Round detail screens carry no back link: the Attendees and Rounds chips are pinned one tap away and lead to the same place. The Poll screen and the organiser flags screen keep theirs, because the nav reaches neither the Votes list nor the organiser area. Do not add a back link to a screen whose parent is a nav chip.

The Event screen lists no destinations either — the nav owns Rounds, Standings, Attendees and Schedule. What it carries instead is the conditional calls-to-action the nav deliberately does not: the open vote, and My game, which is read only for a viewer who has entered (`viewer.is_attendee`) and shown only while `/my-game` returns a Game. One consequence to know: with the list group gone, the Votes list is reachable only through the "Voting is open" call-to-action.
