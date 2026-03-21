<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('it stores a photo with file and thumbnail on disk', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('gallery.store'), [
            'name' => 'My Photo',
            'description' => 'A test photo',
            'photo' => UploadedFile::fake()->image('photo.jpg', 800, 600),
        ])
        ->assertSuccessful();

    $data = $response->json('data');

    expect($data['name'])->toBe('My Photo')
        ->and($data['description'])->toBe('A test photo')
        ->and($data['url'])->not->toBeNull()
        ->and($data['thumbnail_url'])->not->toBeNull();

    $photo = $user->photos()->first();
    Storage::disk('public')->assertExists($photo->path);
    Storage::disk('public')->assertExists($photo->thumbnail_path);
});

test('it validates required fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('gallery.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'photo']);
});

test('it rejects invalid file types', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('gallery.store'), [
            'name' => 'My Photo',
            'photo' => UploadedFile::fake()->create('document.pdf', 100),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('photo');
});

test('it rejects files exceeding max size', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('gallery.store'), [
            'name' => 'My Photo',
            'photo' => UploadedFile::fake()->image('large.jpg')->size(11000),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('photo');
});
