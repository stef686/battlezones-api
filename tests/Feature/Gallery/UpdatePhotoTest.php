<?php

use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('it updates photo fields', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    $this->actingAs($user)
        ->patchJson(route('gallery.update', $photo), [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.description', 'Updated description');
});

test('it replaces file and thumbnail when new photo uploaded', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $oldFile = UploadedFile::fake()->image('old.jpg', 800, 600);
    $oldPath = $oldFile->store("photos/{$user->id}", 'public');
    $oldThumbPath = "photos/{$user->id}/thumbs/old-thumb.jpg";
    Storage::disk('public')->put($oldThumbPath, 'thumb');

    $photo = Photo::factory()->for($user)->create([
        'path' => $oldPath,
        'thumbnail_path' => $oldThumbPath,
    ]);

    $this->actingAs($user)
        ->patchJson(route('gallery.update', $photo), [
            'photo' => UploadedFile::fake()->image('new.jpg', 800, 600),
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['id', 'url', 'thumbnail_url']]);

    $photo->refresh();

    expect($photo->path)->not->toBe($oldPath);
    expect($photo->thumbnail_path)->not->toBe($oldThumbPath);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertMissing($oldThumbPath);
    Storage::disk('public')->assertExists($photo->path);
    Storage::disk('public')->assertExists($photo->thumbnail_path);
});

test('it returns 403 for another user photo', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->patchJson(route('gallery.update', $photo), [
            'name' => 'Hacked',
        ])
        ->assertForbidden();
});
