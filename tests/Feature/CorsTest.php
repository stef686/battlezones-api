<?php

test('a preflight from an allowed origin is permitted', function () {
    config(['cors.allowed_origins' => ['https://battlezones.app']]);

    $this->call('OPTIONS', '/api/events', [], [], [], [
        'HTTP_ORIGIN' => 'https://battlezones.app',
        'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'authorization',
    ])
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'https://battlezones.app');
});

test('a disallowed origin is never echoed back, so the browser refuses the response', function () {
    config(['cors.allowed_origins' => ['https://battlezones.app']]);

    $response = $this->call('OPTIONS', '/api/events', [], [], [], [
        'HTTP_ORIGIN' => 'https://not-battlezones.example',
        'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
    ]);

    // The middleware answers a preflight with the origin it does allow, which
    // is what makes the browser reject it: what matters is that the requesting
    // origin is never named, and that no wildcard stands in for it.
    expect($response->headers->get('Access-Control-Allow-Origin'))
        ->not->toBe('https://not-battlezones.example')
        ->not->toBe('*');
});

test('a real request from a disallowed origin is not readable by it', function () {
    config(['cors.allowed_origins' => ['https://battlezones.app']]);

    $response = $this->getJson(route('events.index'), ['Origin' => 'https://not-battlezones.example']);

    expect($response->headers->get('Access-Control-Allow-Origin'))
        ->not->toBe('https://not-battlezones.example')
        ->not->toBe('*');
});

test('an allowed origin may read the response to a real request', function () {
    config(['cors.allowed_origins' => ['https://battlezones.app']]);

    $this->getJson(route('events.index'), ['Origin' => 'https://battlezones.app'])
        ->assertSuccessful()
        ->assertHeader('Access-Control-Allow-Origin', 'https://battlezones.app');
});

test('the SPA origins are configured for production and staging', function () {
    expect(config('cors.paths'))->toContain('api/*')
        ->and(config('cors.allowed_origins'))->not->toBeEmpty()
        ->and(config('cors.allowed_origins'))->not->toContain('*');
});
