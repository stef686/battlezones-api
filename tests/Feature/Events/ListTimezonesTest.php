<?php

test('anybody picking a timezone reads exactly the zones an event may be created in', function () {
    $response = $this->getJson(route('timezones.index'))->assertSuccessful();

    expect($response->json('data'))->toBe(DateTimeZone::listIdentifiers())
        ->toContain('Europe/London', 'Asia/Kolkata', 'UTC');
});
