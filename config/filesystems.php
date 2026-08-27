<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Uploads Disk
    |--------------------------------------------------------------------------
    |
    | The disk every user upload — Photos, Banners, Event Documents, Event
    | Update attachments — is written to and read back from. Development and
    | the test suite use "uploads_local"; production points this at "r2".
    | Nothing should name a disk directly: go through UploadStorage.
    |
    | Uploads are private wherever they live, so a URL to one is signed and
    | expires. The TTL is in minutes, and doubles as the window a signed URL is
    | reused for, so the same file yields the same URL to every caller within
    | it and stays cacheable.
    |
    */

    'uploads' => env('UPLOADS_DISK', 'uploads_local'),

    'uploads_url_ttl' => (int) env('UPLOADS_URL_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        /*
         * What "r2" stands in for in development and in the test suite: files
         * off the web root, reached only through an expiring signed URL, so a
         * link that would not survive production does not work here either.
         * Served by Laravel rather than by the /storage symlink, which is why
         * it does not — and must not — share a URI with the "public" disk.
         */
        'uploads_local' => [
            'driver' => 'local',
            'root' => storage_path('app/uploads'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/uploads',
            'visibility' => 'private',
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        /*
         * Cloudflare R2, which speaks S3. The region is always "auto" and the
         * endpoint is the account-scoped one R2 gives you; there is no
         * public URL because the bucket is private and every URL is signed.
         */
        'r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => env('R2_DEFAULT_REGION', 'auto'),
            'bucket' => env('R2_BUCKET'),
            'endpoint' => env('R2_ENDPOINT'),
            'use_path_style_endpoint' => env('R2_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'private',
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
