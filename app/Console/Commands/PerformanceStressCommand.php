<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PerformanceStressCommand extends Command
{
    protected $signature = 'agora:performance-stress
                            {--scale=100000 : Published resources to seed (0 skips)}
                            {--skip-scale : Skip agora:performance-scale}
                            {--profiles=standard,heavy,stress : Comma-separated simulate-load profiles}';

    protected $description = 'Scale dataset, benchmark, and run multiple load-test profiles';

    public function handle(): int
    {
        if (! $this->option('skip-scale')) {
            $scale = max(0, (int) $this->option('scale'));

            if ($scale > 0) {
                $this->info("Seeding {$scale} published resources…");

                if ($this->call('agora:performance-scale', ['--count' => $scale]) !== self::SUCCESS) {
                    return self::FAILURE;
                }
            }
        }

        $this->call('cache:clear');
        $this->newLine();
        $this->info('Recording performance baseline…');
        $this->call('agora:performance-benchmark');

        $profiles = array_filter(array_map('trim', explode(',', (string) $this->option('profiles'))));
        $exitCode = self::SUCCESS;

        foreach ($profiles as $profile) {
            $this->newLine();
            $this->info("=== Load profile: {$profile} ===");

            $result = $this->call('agora:simulate-load', [
                '--profile' => $profile,
            ]);

            if ($result !== self::SUCCESS) {
                $exitCode = self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('Stress run complete. Baseline: '.config('agora.performance.benchmark_output'));

        File::ensureDirectoryExists(storage_path('app/performance'));

        return $exitCode;
    }
}
