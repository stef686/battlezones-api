<?php

use App\Models\User;

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
