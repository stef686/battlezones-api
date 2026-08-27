<?php

use App\Models\Photo;
use App\Models\User;
use App\Services\UploadStorage;
use Illuminate\Support\Facades\Storage;

test('it deletes the photo model and files from disk', function () {
    Storage::fake(UploadStorage::name());
    $user = User::factory()->create();

    $path = 'photos/1/test.jpg';
    $thumbPath = 'photos/1/thumbs/test.jpg';
    UploadStorage::disk()->put($path, 'file');
    UploadStorage::disk()->put($thumbPath, 'thumb');

    $photo = Photo::factory()->for($user)->create([
        'path' => $path,
        'thumbnail_path' => $thumbPath,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('gallery.destroy', $photo))
        ->assertJson(['message' => 'Photo successfully deleted.']);

    expect(Photo::find($photo->id))->toBeNull();
    UploadStorage::disk()->assertMissing($path);
    UploadStorage::disk()->assertMissing($thumbPath);
});

test('it returns 404 json when photo does not exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->deleteJson(route('gallery.destroy', ['photo' => 999]))
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});

test('it returns 403 for another user photo', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->deleteJson(route('gallery.destroy', $photo))
        ->assertForbidden();
});
