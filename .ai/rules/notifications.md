---
paths:
  - 'app/Notifications/**'
---

# Notifications

## Event notifications always reach the app
`NotificationType::alwaysInApp()` marks the Event types (RoundLive, ResultActivity, VotingOpen). `User::getNotificationDrivers()` prepends the `database` driver for those, so a preference adds email or push and can never remove the in-app notification. New Event notification types must return true there. Transactional mail — invites, army list unlocks, feedback requests — declares `via() = ['mail']` directly and stays outside the preference system: gating it lets a Player switch off their only way in. Payloads carry ids and a type, never denormalised names.
