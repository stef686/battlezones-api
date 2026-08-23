---
paths:
  - app/Actions/Events/GenerateRoundPairings.php
  - app/Services/HungarianMatcher.php
  - app/Actions/Events/SwapRoundPairings.php
---

# Pairing

## Standings for pairing are summed from Game Scores, not read from `event_standings`
`GenerateRoundPairings` ranks the field by aggregating `game_scores` over the Event's Rounds in one query. It does not read `EventStanding` rows, which are still written by hand and can be stale or absent. When computed standings land, both should come from the same place — until then, do not "simplify" the action into reading `event_standings`, or pairing silently starts using numbers nobody refreshed.

Score *groups* come from the leading ranking Score Type only (Match Points), while rank order uses every ranking Score Type in `ranking_order` precedence. Grouping on the full tuple would put two Attendees on the same record in different groups over a Victory Point margin, and every Game would then look like a pair-down.

## Byes are exempt from the results gate and carry no scores
Generation is blocked when any Attendee in the previous Round has no `game_scores` row, but Games with `is_bye` are skipped: nothing was contested, and a Bye's Match Points are derived from `is_bye` rather than submitted. `GenerateRoundPairings` awards the Bye its Match Points the moment it creates the Game, via `StoreGameScores::awardByeWin()`, so the Attendee ranks correctly straight away; only the Victory Points wait on an Organiser. Do not "fix" the gate by requiring Bye results — it would block the next Round on a score no one can submit.

## The rematch penalty is computed, not a magic number
The cost matrix is squared score-group distance plus a rematch penalty of `fieldSize ** 3 + 1`. That is large enough to dominate every possible arrangement of the rest of the matrix, so a rematch is never chosen while any alternative exists, and finite so that a genuinely stuck field still returns a pairing to be flagged. Replacing it with a fixed constant breaks the first property on large fields.

## Events that do not oppose Allegiances are paired by folding the ranked field
Matching within one set is general graph matching (blossom), which is not implemented. `divide()` instead splits the ranked field top-half against bottom-half so the graph stays bipartite and the Hungarian solve remains exact. The fold is the classic Swiss arrangement, but it does restrict the solution space: two top-half Attendees can never meet, however cheap that pairing would be.

## A swap exchanges the second Attendee of each Game
`SwapRoundPairings` keeps each Game's first Attendee (pivot order) and exchanges the second. Under an opposed-allegiance Event that is what keeps both Games opposed; the alternative recombination pairs like against like. Do not add a free-form "put this team against that team" endpoint — it is a different feature with a different guard set. Swapping with a Bye moves the Bye instead, and the incoming bye Attendee must be on the majority Allegiance or the Round cannot be paired.
