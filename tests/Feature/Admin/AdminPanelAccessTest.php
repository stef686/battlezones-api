<?php

use App\Filament\Widgets\LatestUsersWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use App\Models\User;
use Filament\Pages\Dashboard;
use Livewire\Livewire;

test('admin user can access the admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

test('non-admin user is denied access to the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('unauthenticated user is redirected to admin login', function () {
    $this->get('/admin')
        ->assertRedirect('/admin/login');
});

test('dashboard renders with all widgets', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Dashboard::class)
        ->assertSeeLivewire(StatsOverviewWidget::class)
        ->assertSeeLivewire(LatestUsersWidget::class)
        ->assertSeeLivewire(UpcomingEventsWidget::class);
});
