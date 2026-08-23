---
paths:
  - 'app/Http/Controllers/Events/**'
---

# Events

## A Game result is claimed once, by conditional update
First submission wins. StoreGameResultController claims the Game with `Game::whereKey(...)->whereNull('submitted_at')->update(...)` and 409s when zero rows change — that conditional update is the entire race guard, so never replace it with a read-then-write or a `$game->update()`. There is deliberately no self-correction path: a wrong score is fixed by flagging it for an Organiser, not by resubmitting.
