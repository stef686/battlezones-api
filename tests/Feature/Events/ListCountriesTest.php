<?php

use App\Enums\Country;

test('anybody picking a country reads every one the platform accepts, by name', function () {
    $response = $this->getJson(route('countries.index'))->assertSuccessful();

    $response->assertJsonCount(count(Country::cases()), 'data')
        ->assertJsonPath('data.0', ['code' => 'AF', 'name' => 'Afghanistan'])
        ->assertJsonPath('data.1', ['code' => 'AX', 'name' => 'Åland Islands']);

    expect($response->json('data'))->toContain(['code' => 'GB', 'name' => Country::UnitedKingdom->label()]);
});
