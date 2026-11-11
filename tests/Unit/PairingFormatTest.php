<?php

use App\Enums\PairingFormat;

test('only swiss is built, so only swiss can be chosen for an event', function () {
    expect(array_values(array_filter(PairingFormat::cases(), fn (PairingFormat $format): bool => $format->isAvailable())))
        ->toBe([PairingFormat::Swiss]);
});
