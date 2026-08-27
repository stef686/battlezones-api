<?php

use App\Services\UploadStorage;
use Illuminate\Support\Facades\Storage;

test('it signs an upload url, because the bucket holding it is private', function () {
    config()->set('filesystems.uploads', 'uploads_local');

    $url = UploadStorage::url('photos/1/a.jpg');

    expect($url)->toContain('signature=')->toContain('expires=');
});

test('it hands every caller the same url for a file within the window', function () {
    Storage::fake(UploadStorage::name());

    $first = UploadStorage::url('photos/1/a.jpg');
    $this->travel(30)->minutes();

    expect(UploadStorage::url('photos/1/a.jpg'))->toBe($first);
});

test('it keeps an url alive for at least the configured ttl', function () {
    Storage::fake(UploadStorage::name());
    config()->set('filesystems.uploads_url_ttl', 60);

    parse_str((string) parse_url(UploadStorage::url('photos/1/a.jpg'), PHP_URL_QUERY), $query);

    expect((int) $query['expiration'])->toBeGreaterThanOrEqual(now()->addMinutes(60)->getTimestamp());
});

test('it writes to whichever disk is configured, not a named one', function () {
    config()->set('filesystems.uploads', 'public');
    Storage::fake('public');

    UploadStorage::disk()->put('a.txt', 'x');

    Storage::disk('public')->assertExists('a.txt');
});
