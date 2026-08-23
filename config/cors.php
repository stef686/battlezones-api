<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| The SPA is served from its own origin (see ADR-0001), so the API has to
| name the origins allowed to read its responses. Every client authenticates
| with a bearer token rather than a cookie, so credentials stay off: a
| wildcard origin would otherwise be refused by the browser outright, and
| naming the origins is what keeps another site from reading a Player's data
| through their browser.
|
*/

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        trim(...),
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', (string) env('FRONTEND_URL', 'http://localhost:5173'))),
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    // Bearer tokens, never cookies. Turning this on would also forbid the
    // wildcard fallbacks browsers apply, and buy nothing we use.
    'supports_credentials' => false,

];
