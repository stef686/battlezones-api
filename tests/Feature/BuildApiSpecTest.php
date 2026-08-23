<?php

use App\Console\Commands\BuildApiSpec;
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
    $before = file_get_contents($committed);

    try {
        $this->artisan('docs:spec', ['--skip-generate' => true])->assertSuccessful();

        /** @var array<string, mixed> $spec */
        $spec = Yaml::parseFile($committed);

        expect($spec['openapi'])->toStartWith('3.')
            ->and($spec['paths'])->not->toBeEmpty()
            ->and(file_get_contents($committed))->not->toContain("\n                    example:");
    } finally {
        file_put_contents($committed, $before);
    }
});
