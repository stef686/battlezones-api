<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Writes the committed OpenAPI spec the SPA generates its types from.
 *
 * Scribe builds its examples from factory-created models, so ids, timestamps
 * and faker values differ on every run. Those examples are noise to a type
 * generator and would make the committed spec churn on every regeneration,
 * so they are stripped: what is committed is the shape, which only moves when
 * the API actually moves.
 */
class BuildApiSpec extends Command
{
    protected $signature = 'docs:spec {--skip-generate : Reuse the spec Scribe last emitted rather than regenerating it}';

    protected $description = 'Generate the API documentation and write the committed OpenAPI spec';

    public const COMMITTED_PATH = 'docs/openapi.yaml';

    private const FAKER_SEED = 20260823;

    private const SCRIBE_PATH = 'storage/app/private/scribe/openapi.yaml';

    public function handle(): int
    {
        if (! $this->option('skip-generate')) {
            // Scribe infers each response shape from a factory-made model, so
            // an unseeded Faker can hand it a null one run and a string the
            // next, moving `nullable` in and out of the committed spec.
            fake()->seed(self::FAKER_SEED);

            $this->call('scribe:generate', ['--no-interaction' => true]);
        }

        $source = base_path(self::SCRIBE_PATH);

        if (! File::exists($source)) {
            $this->error('Scribe has not emitted a spec at '.self::SCRIBE_PATH.'.');

            return self::FAILURE;
        }

        /** @var array<string, mixed> $spec */
        $spec = Yaml::parseFile($source);

        File::put(
            base_path(self::COMMITTED_PATH),
            Yaml::dump(self::withoutExamples($spec), 20, 2, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE),
        );

        $this->info('Wrote '.self::COMMITTED_PATH.'.');

        return self::SUCCESS;
    }

    /**
     * @param  array<mixed>  $spec
     * @return array<mixed>
     */
    public static function withoutExamples(array $spec): array
    {
        $stripped = [];

        foreach ($spec as $key => $value) {
            if ($key === 'example' || $key === 'examples') {
                continue;
            }

            $stripped[$key] = is_array($value) ? self::withoutExamples($value) : $value;
        }

        return $stripped;
    }
}
