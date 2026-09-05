<?php

use App\Models\GameSystem;

test('anybody picking a game system reads the ones the platform knows, in name order', function () {
    GameSystem::factory()->create(['name' => 'Warhammer 40,000', 'slug' => 'warhammer-40000']);
    GameSystem::factory()->create(['name' => 'Age of Sigmar', 'slug' => 'age-of-sigmar']);

    $this->getJson(route('game-systems.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Age of Sigmar')
        ->assertJsonPath('data.0.slug', 'age-of-sigmar')
        ->assertJsonPath('data.1.name', 'Warhammer 40,000');
});
