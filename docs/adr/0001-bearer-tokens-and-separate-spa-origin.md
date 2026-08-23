# Bearer tokens for all clients, SPA served from a separate origin

The web client is a Vue SPA deployed to `battlezones.app`, separate from the Laravel API at
`api.battlezones.app`, and long term the same web build is wrapped by Capacitor as a native app.
Native clients run from `capacitor://localhost` and can never be a stateful first-party origin, so
we authenticate **every** client with Sanctum bearer tokens and treat Sanctum's cookie-session mode
as unused.

## Considered options

- **Same-origin SPA served by Laravel, using Sanctum cookie sessions.** Rejected: the native app
  would still need bearer tokens, so login, refresh, logout and every request interceptor would fork
  into two paths — one of which only ever gets exercised on one platform.
- **Cookie sessions on web, bearer tokens on native.** Rejected for the same reason, with the added
  cost of CSRF handling and `SANCTUM_STATEFUL_DOMAINS` upkeep on a cross-origin setup.

## Consequences

- `EnsureFrontendRequestsAreStateful::fromFrontend()` in `LoginTokenController` is dead code for our
  clients. It is deliberate, not an oversight — do not "fix" the API by making the SPA stateful.
- The access token lives in `localStorage` on web (and `@capacitor/preferences`, later secure
  storage, on native), which accepts XSS exposure that an httpOnly cookie would avoid. A strict CSP
  and no third-party scripts in the SPA are the mitigation, and are load-bearing rather than
  optional.
- `config/cors.php` must be published and scoped to the SPA origins; the API is now
  environment-aware of the frontend.
- Emailed links can no longer point at API routes that return JSON. Opaque-token links
  (invites, feedback, password reset) point at the SPA via `app.frontend_url`; Laravel-signed links
  stay on the API domain, because the signature only validates on the host that generated it, and
  redirect to the SPA once processed.
