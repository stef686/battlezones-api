<?php

use App\Models\Photo;
use App\Models\User;
use App\Services\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('it updates photo fields', function () {
    Storage::fake(UploadStorage::name());
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
    Storage::fake(UploadStorage::name());
    $user = User::factory()->create();

    $oldFile = UploadedFile::fake()->image('old.jpg', 800, 600);
    $oldPath = $oldFile->store("photos/{$user->id}", UploadStorage::name());
    $oldThumbPath = "photos/{$user->id}/thumbs/old-thumb.jpg";
    UploadStorage::disk()->put($oldThumbPath, 'thumb');

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

    UploadStorage::disk()->assertMissing($oldPath);
    UploadStorage::disk()->assertMissing($oldThumbPath);
    UploadStorage::disk()->assertExists($photo->path);
    UploadStorage::disk()->assertExists($photo->thumbnail_path);
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
