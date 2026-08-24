<?php

test('login endpoint is rate limited after 5 attempts per minute', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson(route('login.token'), [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
            'device_name' => 'Test Device',
        ]);
    }

    $this->postJson(route('login.token'), [
        'email' => 'nonexistent@example.com',
        'password' => 'wrong-password',
        'device_name' => 'Test Device',
    ])->assertTooManyRequests();
});

test('register endpoint is rate limited after 5 attempts per minute', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson(route('register'), [
            'email' => 'repeated@example.com',
            'password' => 'short',
        ]);
    }

    $this->postJson(route('register'), [
        'email' => 'repeated@example.com',
        'password' => 'short',
    ])->assertTooManyRequests();
});

test('the login limiter treats one address as one bucket whatever its case', function () {
    foreach (['ada@example.com', 'Ada@Example.com', 'ADA@EXAMPLE.COM', 'ada@example.com ', 'Ada@example.com'] as $email) {
        $this->postJson(route('login.token'), [
            'email' => $email,
            'password' => 'wrong-password',
            'device_name' => 'Test Device',
        ]);
    }

    $this->postJson(route('login.token'), [
        'email' => 'ada@example.com',
        'password' => 'wrong-password',
        'device_name' => 'Test Device',
    ])->assertTooManyRequests();
});

test('two Invites do not share a budget, so a queue at the door does not lock itself out', function () {
    // One Invite hammered to its limit...
    for ($i = 0; $i < 30; $i++) {
        $this->getJson(route('invites.show', ['token' => 'first-token']));
    }

    $this->getJson(route('invites.show', ['token' => 'first-token']))->assertTooManyRequests();

    // ...leaves the next Captain in the queue with a full budget of their own.
    $this->getJson(route('invites.show', ['token' => 'second-token']))->assertNotFound();
});

test('a single Invite token being hammered is still limited', function () {
    for ($i = 0; $i < 30; $i++) {
        $this->getJson(route('invites.show', ['token' => 'one-token']));
    }

    $this->getJson(route('invites.show', ['token' => 'one-token']))->assertTooManyRequests();
});

test('two feedback links do not share a budget', function () {
    for ($i = 0; $i < 30; $i++) {
        $this->getJson(route('feedback.show', ['token' => 'first-feedback-token']));
    }

    $this->getJson(route('feedback.show', ['token' => 'first-feedback-token']))->assertTooManyRequests();

    $this->getJson(route('feedback.show', ['token' => 'second-feedback-token']))->assertNotFound();
});

test('claiming and entering an Invite are budgeted per Invite, not per address', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson(route('invites.session', ['token' => 'busy-token']), ['device_name' => 'iPhone']);
    }

    $this->postJson(route('invites.session', ['token' => 'busy-token']), ['device_name' => 'iPhone'])
        ->assertTooManyRequests();

    $this->postJson(route('invites.session', ['token' => 'quiet-token']), ['device_name' => 'iPhone'])
        ->assertNotFound();
});

test('an IP ceiling still stands behind the per-token budgets', function () {
    // Each token has room to spare; the ceiling is what stops one machine
    // walking through a stolen list of tokens from one seat.
    for ($i = 0; $i < 120; $i++) {
        $this->getJson(route('invites.show', ['token' => "token-{$i}"]));
    }

    $this->getJson(route('invites.show', ['token' => 'a-fresh-token']))->assertTooManyRequests();
});
