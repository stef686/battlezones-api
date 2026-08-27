<?php

use App\Models\Event;
use App\Models\User;
use App\Services\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an organiser uploads a banner and it comes back on the event, normalised to one shape', function () {
    Storage::fake(UploadStorage::name());

    $event = Event::factory()->published()->create();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => UploadedFile::fake()->image('hall.jpg', 2400, 1000),
        ])
        ->assertSuccessful();

    $banner = $this->getJson(route('events.show', ['event' => $event->slug]))
        ->assertSuccessful()
        ->json('data.banner');

    expect($banner)->toHaveKeys(['large', 'small']);

    $stored = UploadStorage::disk()->path(
        (string) $event->refresh()->banner_path,
    );

    [$width, $height] = getimagesize($stored);

    expect([$width, $height])->toEqual([1600, 534]);
});

test('both variants are stored, and the small one is the small one', function () {
    Storage::fake(UploadStorage::name());

    $event = Event::factory()->published()->create();

    $this->actingAs(organiserOf($event))
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => UploadedFile::fake()->image('hall.jpg', 2400, 1000),
        ])
        ->assertSuccessful();

    $event->refresh();

    expect(getimagesize(UploadStorage::disk()->path((string) $event->banner_path)))
        ->toMatchArray([0 => 1600, 1 => 534])
        ->and(getimagesize(UploadStorage::disk()->path((string) $event->banner_small_path)))
        ->toMatchArray([0 => 800, 1 => 267]);
});

test('the crop keeps the top of the image, where nothing is overlaid', function () {
    Storage::fake(UploadStorage::name());

    $event = Event::factory()->published()->create();

    $this->actingAs(organiserOf($event))
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => twoTonedUpload(1600, 1600),
        ])
        ->assertSuccessful();

    $stored = imagecreatefromwebp(
        UploadStorage::disk()->path((string) $event->refresh()->banner_path),
    );

    // A square source cropped to 3:1 keeps only the first third. Centred, the
    // banner would be the bottom colour, which is where the Event's name sits.
    $colour = imagecolorsforindex($stored, imagecolorat($stored, 800, 267));

    expect($colour['red'])->toBeGreaterThan(200)
        ->and($colour['blue'])->toBeLessThan(60);
});

test('replacing a banner takes the old files off the disk first', function () {
    Storage::fake(UploadStorage::name());

    $event = Event::factory()->published()->create();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => UploadedFile::fake()->image('first.jpg', 2400, 1000),
        ])
        ->assertSuccessful();

    $first = [$event->refresh()->banner_path, $event->banner_small_path];

    $this->actingAs($organiser)
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => UploadedFile::fake()->image('second.jpg', 2400, 1000),
        ])
        ->assertSuccessful();

    $event->refresh();

    expect($event->banner_path)->not->toBe($first[0]);

    UploadStorage::disk()->assertMissing($first);
    UploadStorage::disk()->assertExists([$event->banner_path, $event->banner_small_path]);
});

test('removing a banner clears the record and the disk, returning the header to its flat state', function () {
    Storage::fake(UploadStorage::name());

    $event = Event::factory()->published()->create();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => UploadedFile::fake()->image('hall.jpg', 2400, 1000),
        ])
        ->assertSuccessful();

    $stored = [$event->refresh()->banner_path, $event->banner_small_path];

    $this->actingAs($organiser)
        ->deleteJson(route('events.banner.destroy', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.banner', null);

    $event->refresh();

    expect($event->banner_path)->toBeNull()
        ->and($event->banner_small_path)->toBeNull();

    UploadStorage::disk()->assertMissing($stored);
});

test('an event with no banner says so rather than guessing', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.show', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.banner', null);
});

test('a scriptable document, a moving image, and a file that is neither are all refused', function () {
    Storage::fake(UploadStorage::name());

    $event = Event::factory()->published()->create();
    $organiser = organiserOf($event);

    $refused = [
        'svg' => UploadedFile::fake()->create('logo.svg', 4, 'image/svg+xml'),
        'gif' => UploadedFile::fake()->create('banner.gif', 40, 'image/gif'),
        'pdf' => UploadedFile::fake()->create('pack.pdf', 40, 'application/pdf'),
    ];

    foreach ($refused as $file) {
        $this->actingAs($organiser)
            ->post(route('events.banner.store', ['event' => $event->slug]), ['banner' => $file])
            ->assertJsonValidationErrors(['banner']);
    }

    expect($event->refresh()->banner_path)->toBeNull();
});

test('an upload too large for venue wifi, or too small to fill the header, is refused', function () {
    Storage::fake(UploadStorage::name());

    $event = Event::factory()->published()->create();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => UploadedFile::fake()->image('huge.jpg', 4000, 2000)->size(9000),
        ])
        ->assertJsonValidationErrors(['banner']);

    // A square club logo, which is plausibly the first thing anyone tries.
    $this->actingAs($organiser)
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => UploadedFile::fake()->image('logo.png', 512, 512),
        ])
        ->assertJsonValidationErrors(['banner']);

    expect($event->refresh()->banner_path)->toBeNull();
});

test('only an organiser of this event may put a banner on it or take one off', function () {
    Storage::fake(UploadStorage::name());

    $event = Event::factory()->published()->create();
    $stranger = User::factory()->create();

    $this->postJson(route('events.banner.store', ['event' => $event->slug]))
        ->assertUnauthorized();

    $this->actingAs($stranger)
        ->post(route('events.banner.store', ['event' => $event->slug]), [
            'banner' => UploadedFile::fake()->image('hall.jpg', 2400, 1000),
        ])
        ->assertForbidden();

    $this->actingAs($stranger)
        ->deleteJson(route('events.banner.destroy', ['event' => $event->slug]))
        ->assertForbidden();
});

/**
 * A source whose top half is red and bottom half is blue, so a test can tell
 * which end of it survived the crop.
 */
function twoTonedUpload(int $width, int $height): UploadedFile
{
    $image = imagecreatetruecolor($width, $height);

    imagefilledrectangle($image, 0, 0, $width, intdiv($height, 2), imagecolorallocate($image, 220, 30, 30));
    imagefilledrectangle($image, 0, intdiv($height, 2), $width, $height, imagecolorallocate($image, 30, 30, 220));

    $path = tempnam(sys_get_temp_dir(), 'banner').'.png';
    imagepng($image, $path);

    return new UploadedFile($path, 'source.png', 'image/png', null, true);
}
