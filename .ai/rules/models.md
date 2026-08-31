---
paths:
  - app/Models/Event.php
---

# Models

## Several Score Types may be flagged primary; the first wins
`event_score_types.is_primary` is deliberately unconstrained — no unique index, no Filament save hook clearing the others. `Event::primaryScoreType()` takes the first flagged one in display order, and the Filament toggle says so. Do not "fix" this with a partial unique index or a validation rule.

Resources always send `is_primary` already resolved, so exactly one column carries `true` on the wire however many carry it in the table.
