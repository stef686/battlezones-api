<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

/**
 * Runs Pint over the models once ide-helper has rewritten their docblocks.
 *
 * ide-helper's `post_migrate` hook regenerates every model's docblock after a
 * migration, and what it writes does not match the project's Pint rules. This
 * is the second half of that hook, so the models are left formatted rather
 * than dirty after every migrate.
 */
class FormatModels extends Command
{
    protected $signature = 'format:models';

    protected $description = 'Format the models with Pint after ide-helper rewrites their docblocks';

    public function handle(): int
    {
        $result = Process::run([base_path('vendor/bin/pint'), base_path('app/Models')]);

        $this->output->write($result->output());

        return $result->successful() ? self::SUCCESS : self::FAILURE;
    }
}
