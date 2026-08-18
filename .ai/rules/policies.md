---
paths:
  - 'app/Policies/**'
---

# Policies

## A new policy silently gates Filament actions
Filament resolves policies by model, so adding `App\Policies\SomethingPolicy::update()` immediately hides the Edit action in every Filament table for that model — the panel does not check abilities the policy does not define, but it does check the ones it does. Any policy on a model that has a Filament resource or relation manager must let `$user->is_admin` through, or the admin repair path disappears with no error (the test failure reads "action [edit] is not visible").
