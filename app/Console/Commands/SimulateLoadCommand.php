<?php

namespace App\Console\Commands;

use App\Models\Resource;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class SimulateLoadCommand extends Command
{
    protected $signature = 'agora:simulate-load
                            {--users= : Total simulated requests (default from --profile)}
                            {--concurrency= : Parallel requests per batch (default from --profile)}
                            {--profile=standard : Preset: standard, heavy, stress, extreme}
                            {--base-url= : API base URL (default: AGORA_SIMULATE_BASE_URL, http://nginx in Docker, else APP_URL)}
                            {--mix=list:70,show:25,lookups:5 : Endpoint mix percentages}
                            {--from-app-container : Allow high concurrency when exec runs inside the app container}';

    protected $description = 'Simulate concurrent API traffic (list, show, lookups) and report latency';

    public function handle(): int
    {
        $parameters = $this->resolveLoadParameters();

        if ($parameters === null) {
            return self::FAILURE;
        }

        [$total, $concurrency] = $parameters;
        $total = max(1, $total);
        $concurrency = max(1, min($concurrency, 250));
        $baseUrl = rtrim($this->resolveBaseUrl(), '/');
        $mix = $this->parseMix($this->option('mix'));

        if ($this->runsInsideAppContainer() && ! $this->option('from-app-container') && $concurrency > 50) {
            $this->warn('High concurrency inside the app container competes with PHP-FPM. Capping at 50.');
            $this->line('Prefer: docker compose exec queue php artisan agora:simulate-load');
            $this->line('Or pass --from-app-container to keep --concurrency='.$concurrency.'.');
            $concurrency = 50;
        }

        $slug = Resource::query()->where('status', 'published')->value('slug');

        if (! $slug) {
            $this->error('No published resources found. Seed the database first.');

            return self::FAILURE;
        }

        $this->info("Simulating {$total} requests ({$concurrency} concurrent) against {$baseUrl}");

        $latencies = [];
        $failures = 0;
        $completed = 0;
        $failureBreakdown = [
            'connection' => 0,
            '429' => 0,
            '502' => 0,
            '504' => 0,
            '5xx' => 0,
            'other' => 0,
        ];
        $devicePool = array_map(fn (int $index) => "sim-device-{$index}", range(1, 500));
        $sampledServerError = false;

        while ($completed < $total) {
            $batchSize = min($concurrency, $total - $completed);
            $responses = Http::pool(function ($pool) use ($batchSize, $baseUrl, $mix, $slug, $devicePool) {
                $requests = [];

                for ($i = 0; $i < $batchSize; $i++) {
                    $endpoint = $this->pickEndpoint($mix);
                    $url = match ($endpoint) {
                        'show' => "{$baseUrl}/api/v1/resources/{$slug}",
                        'lookups' => "{$baseUrl}/api/v1/lookups/categories",
                        default => "{$baseUrl}/api/v1/resources?per_page=24",
                    };

                    $requests[] = $pool->as((string) $i)
                        ->withHeaders([
                            'X-Device-Id' => $devicePool[random_int(0, count($devicePool) - 1)],
                            'X-Load-Test' => '1',
                        ])
                        ->timeout(30)
                        ->get($url);
                }

                return $requests;
            });

            foreach ($responses as $response) {
                $completed++;

                if ($response instanceof Throwable) {
                    $failures++;
                    $failureBreakdown['connection']++;

                    if ($failures === 1) {
                        $this->warn('First connection error: '.$response->getMessage());
                    }

                    continue;
                }

                if (! $response instanceof Response || $response->failed()) {
                    $failures++;
                    $this->recordHttpFailure($response, $failureBreakdown);

                    if (! $sampledServerError && $response instanceof Response && $response->serverError()) {
                        $sampledServerError = true;
                        $this->warn('Sample server error ('.$response->status().'): '.str($response->body())->limit(200));
                    }

                    continue;
                }

                $transferTime = $response->transferStats?->getTransferTime();

                if ($transferTime !== null) {
                    $latencies[] = $transferTime * 1000;
                }
            }

            $this->output->write('.');
        }

        $this->newLine(2);

        if ($latencies === []) {
            $this->error("All {$failures} requests failed.");
            $this->printFailureBreakdown($failureBreakdown);

            if (str_contains($baseUrl, 'localhost')) {
                $this->line('Tip: from inside Docker use --base-url=http://nginx (or set AGORA_SIMULATE_BASE_URL).');
            }

            return self::FAILURE;
        }

        sort($latencies);

        $this->table(['Metric', 'Value'], [
            ['Total requests', $total],
            ['Failures', $failures],
            ['Success rate', round((($total - $failures) / $total) * 100, 2).'%'],
            ['p50 latency (ms)', round($latencies[(int) floor(count($latencies) * 0.5)], 2)],
            ['p95 latency (ms)', round($latencies[(int) floor(count($latencies) * 0.95)], 2)],
            ['max latency (ms)', round($latencies[array_key_last($latencies)], 2)],
        ]);

        if ($failures > 0) {
            $this->newLine();
            $this->warn('Failure breakdown:');
            $this->printFailureBreakdown($failureBreakdown);

            if ($failureBreakdown['connection'] > 0) {
                $this->line('Connection errors: restart nginx/app after config changes, then verify: curl http://nginx/up');
            }
        }

        return $failures > ($total * 0.01) ? self::FAILURE : self::SUCCESS;
    }

    private function runsInsideAppContainer(): bool
    {
        $hostname = gethostname();

        return is_string($hostname) && str_contains(strtolower($hostname), 'app');
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    private function resolveLoadParameters(): ?array
    {
        $presets = [
            'standard' => [5_000, 100],
            'heavy' => [20_000, 150],
            'stress' => [50_000, 200],
            'extreme' => [100_000, 250],
        ];

        $profile = (string) $this->option('profile');

        if (! array_key_exists($profile, $presets)) {
            $this->error("Unknown profile [{$profile}]. Use: ".implode(', ', array_keys($presets)));

            return null;
        }

        [$defaultUsers, $defaultConcurrency] = $presets[$profile];

        return [
            (int) ($this->option('users') ?? $defaultUsers),
            (int) ($this->option('concurrency') ?? $defaultConcurrency),
        ];
    }

    /**
     * @param  array<string, int>  $failureBreakdown
     */
    private function recordHttpFailure(mixed $response, array &$failureBreakdown): void
    {
        if (! $response instanceof Response) {
            $failureBreakdown['other']++;

            return;
        }

        $status = $response->status();

        match (true) {
            $status === 429 => $failureBreakdown['429']++,
            $status === 502 => $failureBreakdown['502']++,
            $status === 504 => $failureBreakdown['504']++,
            $status >= 500 => $failureBreakdown['5xx']++,
            default => $failureBreakdown['other']++,
        };
    }

    /**
     * @param  array<string, int>  $failureBreakdown
     */
    private function printFailureBreakdown(array $failureBreakdown): void
    {
        $this->table(
            ['Cause', 'Count'],
            collect($failureBreakdown)
                ->filter(fn (int $count) => $count > 0)
                ->map(fn (int $count, string $cause) => [$cause, $count])
                ->values()
                ->all(),
        );
    }

    private function resolveBaseUrl(): string
    {
        if ($this->option('base-url')) {
            return $this->option('base-url');
        }

        if ($url = config('agora.performance.simulate_base_url')) {
            return $url;
        }

        if (file_exists('/.dockerenv')) {
            return 'http://nginx';
        }

        return config('app.url');
    }

    /**
     * @return array<string, int>
     */
    private function parseMix(string $mix): array
    {
        $weights = [];

        foreach (explode(',', $mix) as $part) {
            [$key, $weight] = array_pad(explode(':', trim($part)), 2, 0);
            $weights[$key] = (int) $weight;
        }

        return $weights;
    }

    /**
     * @param  array<string, int>  $mix
     */
    private function pickEndpoint(array $mix): string
    {
        $roll = random_int(1, array_sum($mix));
        $cursor = 0;

        foreach ($mix as $endpoint => $weight) {
            $cursor += $weight;

            if ($roll <= $cursor) {
                return $endpoint;
            }
        }

        return 'list';
    }
}
