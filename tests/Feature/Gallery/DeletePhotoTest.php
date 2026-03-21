<?php

use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('it deletes the photo model and files from disk', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $path = 'photos/1/test.jpg';
    $thumbPath = 'photos/1/thumbs/test.jpg';
    Storage::disk('public')->put($path, 'file');
    Storage::disk('public')->put($thumbPath, 'thumb');

    $photo = Photo::factory()->for($user)->create([
        'path' => $path,
        'thumbnail_path' => $thumbPath,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('gallery.destroy', $photo))
        ->assertNoContent();

    expect(Photo::find($photo->id))->toBeNull();
    Storage::disk('public')->assertMissing($path);
    Storage::disk('public')->assertMissing($thumbPath);
});

test('it returns 403 for another user photo', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->deleteJson(route('gallery.destroy', $photo))
        ->assertForbidden();
});
