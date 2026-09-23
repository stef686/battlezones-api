<?php

use App\Console\Commands\BuildApiSpec;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

test('the committed spec keeps the shape and drops the examples', function () {
    $stripped = BuildApiSpec::withoutExamples([
        'paths' => [
            '/api/events/{event_slug}' => [
                'get' => [
                    'responses' => [
                        200 => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'example' => ['data' => ['id' => 134]],
                                        'properties' => [
                                            'id' => ['type' => 'integer', 'example' => 134],
                                            'name' => ['type' => 'string', 'nullable' => true, 'example' => 'Ada'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $schema = $stripped['paths']['/api/events/{event_slug}']['get']['responses'][200]['content']['application/json']['schema'];

    expect($schema)->toBe([
        'type' => 'object',
        'properties' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'nullable' => true],
        ],
    ]);
});

test('the command writes the committed spec from what Scribe emitted', function () {
    $committed = base_path(BuildApiSpec::COMMITTED_PATH);
    $emitted = base_path(BuildApiSpec::SCRIBE_PATH);
    $committedBefore = file_get_contents($committed);
    $emittedBefore = File::exists($emitted) ? File::get($emitted) : null;

    // Scribe's output is gitignored, so a fresh checkout has none: the test
    // hands the command one rather than depending on a local docs run.
    File::ensureDirectoryExists(dirname($emitted));
    File::put($emitted, Yaml::dump([
        'openapi' => '3.0.3',
        'paths' => [
            '/api/countries' => [
                'get' => [
                    'responses' => [
                        200 => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'example' => ['data' => [['code' => 'GB', 'name' => 'United Kingdom']]],
                                        'properties' => ['data' => ['type' => 'array']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], 20, 2));

    try {
        $this->artisan('docs:spec', ['--skip-generate' => true])->assertSuccessful();

        /** @var array<string, mixed> $spec */
        $spec = Yaml::parseFile($committed);

        expect($spec['openapi'])->toStartWith('3.')
            ->and($spec['paths'])->toHaveKey('/api/countries')
            ->and(file_get_contents($committed))->not->toContain('example');
    } finally {
        file_put_contents($committed, $committedBefore);

        $emittedBefore === null ? File::delete($emitted) : File::put($emitted, $emittedBefore);
    }
});
