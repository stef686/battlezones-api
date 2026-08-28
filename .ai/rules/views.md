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

## A nav-reachable screen keeps its title for screen readers only
Standings, Attendees and Schedule wear their name in the Event nav pinned above them, so a visible `<h1>` repeating it spends a line of a phone viewport saying what the lit tab already says. Those three keep the heading as `class="sr-only"` — a deep link still lands on a named screen for a screen reader — and no screen the nav reaches should get its section name back as a visible title.

This applies only to a fixed section name. A screen titled with content — a Round's name, an Attendee's, a Poll's, the viewer's own team — keeps its visible heading, since the nav cannot say which one you opened. The Rounds tab lands on exactly such a screen: the Round's name is the visible `<h1>`, centred between the chevrons, and the tab saying "Rounds" does not say which Round.

## Back links only where the Event nav cannot reach
The Round screen carries no back link: it *is* what the Rounds tab reaches, so there is nothing behind it to go back to. The Poll screen keeps its back link, because the nav does not reach the Votes list. The organiser screens keep theirs too, even though the Organisers tab now reaches the hub they hang off. The Game screen keeps one to its Round, which the Rounds tab does not reach past. Do not add a back link to a screen the nav itself lands on.

The Attendee screen is the exception that shows what the rule is actually about. It used to carry none — the Attendees tab was pinned one tap away and led to the same place — but the Standings now open a team from its row, so the nav tab is no longer the only way in and a reader who arrived from the Standings had nothing to go back to. A screen the nav reaches *by one route among several* keeps a back link; only a screen the nav lands on directly does without.

## The Rounds tab resolves to a Round; it is not a list
`RoundsView` fetches the Rounds, picks the last published one (`latestPlayable` in `api/rounds.ts`) and `router.replace`s to the Round screen. It renders only the states that have no Round to land on: the Event missing, the read failing, or no Rounds published yet. It replaces rather than pushes, because a resolver left in the history makes the phone's back button bounce off it.

The list it used to render is gone on purpose: a Player taps Rounds to see what is being played right now, and a menu of every Round stood between them and the only one most readers ever want. Moving between Rounds is the chevrons either side of the name on the Round screen, driven by the same cached `keys.rounds` list. Do not reintroduce a Rounds index screen; add to the chevrons instead.

Drafts are stepped over when resolving — only an Organiser is sent one, and landing them on pairings nobody else can see while the played Round sits one chevron behind reads as the Event having moved on. An Organiser whose only Round is a Draft still lands on it, because it is the only Round there is.

The search on the Round screen filters the Games already in hand rather than asking the API per keystroke, and matches team names — the same names the rows show. It deliberately survives moving between Rounds: a Player following one team walks the chevrons with the filter held, and the empty state names the term so a Round that team did not play in explains itself.

The Event screen lists no destinations either — the nav owns Rounds, Standings, Attendees, Schedule and, for an Organiser, the organiser hub. What it carries instead is the conditional calls-to-action the nav deliberately does not: entering the Event, the open vote, and My game, which is read only for a viewer who has entered (`viewer.is_attendee`) and shown only while `/my-game` returns a Game. Running the Event is not among them any more — that is the Organisers tab, and it must not come back as a section on Home. One consequence to know: with the list group gone, the Votes list is reachable only through the "Voting is open" call-to-action.
