---
paths:
  - 'app/Notifications/**'
---

# Notifications

## Event notifications always reach the app
`NotificationType::alwaysInApp()` marks the Event types (RoundLive, ResultActivity, VotingOpen). `User::getNotificationDrivers()` prepends the `database` driver for those, so a preference adds email or push and can never remove the in-app notification. New Event notification types must return true there. Transactional mail — invites, army list unlocks, feedback requests — declares `via() = ['mail']` directly and stays outside the preference system: gating it lets a Player switch off their only way in. Payloads carry ids and a type, never denormalised names. `RemovedFromEventNotification` is transactional too (`mail` for everyone, plus `database` once claimed, type `removed_from_event`) and has no `NotificationType` case, because `NotificationSettingsResource` lists every case as a switch. It carries the Attendee's name as a string, never the Attendee: the delete has removed it before a queued job could load it. Build it with `attendeeDeleted()` (from `DeleteAttendee`) or `memberRemoved()` (from `RemoveAttendeeMember`), which choose the body line. The email names the remover only when the Player knows them — an Organiser of the Event, or for a member removal the team mate who did it; a platform admin reads as "An organiser".
