# Battlezones

A platform for running tabletop wargaming events — registration, pairings, results, and standings — alongside the community features (galleries, conversations, follows) that surround them.

## Language

**Event**:
A single competition run by an organiser, identified publicly by its slug. Every piece of competition data belongs to exactly one Event.
_Avoid_: Tournament, comp

**Attendee**:
The competing unit within an Event. An Attendee is a party of one or more Players — one for a singles event, two for a doubles event — and is what gets paired, scored, and ranked.
_Avoid_: Team, entrant, participant, competitor
_Except_: Player-facing copy calls a Player's own Attendee "my team" (the nav chip, `MyTeamView`, the `my-team` route), because that is the word Players use at a venue. Attendee remains the term everywhere else — API, models, resources, docs — and no code should be renamed to match the copy.

**Player**:
A person taking part in an Event, as a member of exactly one Attendee. Factions and army lists belong to the Player, not the Attendee.
_Avoid_: Attendee, member, user (a User is the platform account; a Player is that account's presence at an Event)

**Organiser**:
A Player trusted to run an Event — publishing Rounds, correcting results, opening Polls, and reading tallies. One Organiser leads and may appoint the others. An Organiser may also compete, so their corrections are recorded against their name rather than forbidden.
_Avoid_: TO, admin (an admin runs the platform; an Organiser runs one Event), host

**Round**:
One numbered stage of an Event in which every Attendee plays at most one Game. A Round is Draft while only organisers can see its Games, and Live once Players can. Live is a latch — earlier Rounds stay Live as later ones are published — so the current Round is the highest-numbered Live one.
_Avoid_: Session, leg

**Game**:
A single contest between Attendees within a Round, assigned a table number.
_Avoid_: Match, matchup, fixture

**Bye**:
A Game with a single Attendee, created when an Event's Attendee count leaves someone unpairable. It counts as a win, and its Victory Points are entered by an organiser rather than submitted by Players.
_Avoid_: Ghost game, walkover

**Pairing**:
The act of deciding which Attendees meet in a Round, and the resulting assignment. Not a stored noun distinct from Game.
_Avoid_: Draw, matchmaking

**Allegiance**:
The side an Attendee fights for in Events whose setting divides the field in two (for Horus Heresy, Loyalist or Traitor). Where an Event opposes allegiances, every Game must be between opposed Attendees, and this outranks keeping Attendees on equal scores together.
_Avoid_: Side, alignment, army

**Score Type**:
A named, per-Event dimension a Game is scored on (e.g. Victory Points, Match Points). An Event defines its own set. A Score Type is either submitted by Players or derived by the system from other scores, never both.
_Avoid_: Metric, category, stat

**Match Points**:
The derived Score Type expressing the outcome of a Game — win, draw, or loss — as a number, so Standings can rank on results before margins. Computed by the system from Victory Points; a bye counts as a win.
_Avoid_: W/L/D, record, points (unqualified)

**Standing**:
An Attendee's ranked position in an Event, with its accumulated total per Score Type. Ranked on Match Points first, with other Score Types as tiebreakers; Attendees on equal scores share a position.
_Avoid_: Leaderboard entry, ranking, placing

**Invite**:
A time-limited credential emailed to one person for one Event, granting access as an unclaimed account. Issued either by the organiser to a Captain, or by a Captain to their partner. Expires shortly after the Event ends, by design, to push the recipient into claiming a real account.
_Avoid_: Magic link (that means permanent passwordless login, which this deliberately is not), invitation email

**Captain**:
The Player who registers an Attendee and invites its other members.
_Avoid_: Owner, team leader, primary player

**Claim**:
The act of an invited Player turning their unclaimed account into a real one by setting a password. Until a Player claims, they exist only as an invited account and stay out of public surfaces.
_Avoid_: Activate, verify, sign up

**Poll**:
A named vote run within an Event, with its own open window, its own limit on how many Attendees a Player may pick, and its own rule for who is eligible to be picked (best-painted army, favourite opponent, and so on).
_Avoid_: Award, competition, survey

**Ballot**:
The complete set of Attendees one Player has picked in one Poll. A Player replaces their whole Ballot rather than casting votes one at a time, and cannot pick the same Attendee twice.
_Avoid_: Vote (a Ballot may hold several), selection

**Banner**:
The single wide image an Organiser sets to give an Event its own face, shown behind the heading at the top of every Event screen. Not a Photo — it is not in the Gallery, is not attributed to a Player, and cannot be reacted to.
_Avoid_: Header image, cover, hero, photo

**Faction**:
The army or force a Player brings, drawn from the Event's Game System.
_Avoid_: Army, race, allegiance
