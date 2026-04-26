<?php

use App\Filament\Widgets\LatestUsersWidget;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('it shows the 5 most recent users and excludes older ones', function () {
    $old = User::factory()->create(['created_at' => now()->subYear()]);

    $recent = User::factory()->count(4)->create([
        'created_at' => now(),
    ]);

    $component = Livewire::test(LatestUsersWidget::class);

    $recent->each(function (User $user) use ($component) {
        $component->assertSeeText($user->name);
        $component->assertSeeText($user->email);
    });

    $component->assertDontSeeText($old->name);
});
