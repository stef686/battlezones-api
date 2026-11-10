# Events are created in one request, and identity is set only at creation

Until issue #134 only admins created Events, through Filament. Organisers now create their own
through a four-step wizard, and the wizard submits **once**: a single JSON `POST /events` that, in
one transaction, creates the Event as Draft, makes the creator its Lead Organiser, and writes its
Score Types through `ReplaceEventScoreTypes`. Nothing exists until the last step is submitted.

## Why one request

Creating a Draft after step one and patching it step by step would survive a refresh, but every
abandoned wizard would leave a half-made Draft behind with a slug taken for good. One request means
an Event either exists whole — with a Lead and a non-empty Score Type set — or not at all.

The Banner is the exception. It goes through its own multipart route (ADR 0003) after the Event
exists, and a failed upload does **not** undo the Event: the Banner is optional, and the Organiser
lands on the Event told to add it in Settings. Folding the file into the create request would mean
nested Score Types inside multipart and the image pipeline running inside the transaction.

## Fields only creation may set

ADR 0002 refuses `slug` and `pairing_format` on `PATCH`, so `POST /events` is the only place either
is ever chosen. That asymmetry is the point, not an oversight:

- **`slug`** is chosen by the Organiser (pre-filled from the name), lowercase letters, digits and
  hyphens, unique, with a short reserved list (`new`, `create`, …) so it cannot shadow the SPA's own
  routes. It is permanent from the moment it exists, so the wizard warns before submit.
- **`pairing_format`** is limited to formats `PairingFormat::isAvailable()` reports as built. Only
  Swiss is today; Round Robin, Knockout and Random (#167, #168, #169) are shown as coming soon
  rather than accepted and silently paired as Swiss.
- **`status`** is not accepted at all. An Event is always created Draft; going public is its own
  transition (#165).

Only claimed Users may create an Event: an unclaimed account expires with the Event it was invited
to, and an Event whose Lead expires has nobody who can appoint Organisers.

## Consequences

- **Slug availability is checked by a dedicated endpoint**, for signed-in Users only, because
  `events.show` answers 404 for Drafts and cannot tell "taken" from "free". That endpoint does
  reveal that some Event — possibly a Draft — holds a slug. Nothing else about it is exposed, and
  the leak is accepted.
- Do not add a field to creation without asking whether `PATCH` should take it too, and if not,
  recording why here.
