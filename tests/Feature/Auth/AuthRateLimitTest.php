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
