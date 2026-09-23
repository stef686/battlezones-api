<?php

use Illuminate\Support\Facades\Process;

test('the command runs pint over the models ide-helper just rewrote', function () {
    Process::fake();

    $this->artisan('format:models')->assertSuccessful();

    Process::assertRan(fn ($process) => $process->command === [base_path('vendor/bin/pint'), base_path('app/Models')]);
});

test('the command fails when pint does', function () {
    Process::fake(['*' => Process::result(exitCode: 1)]);

    $this->artisan('format:models')->assertFailed();
});
