<?php

namespace App\Console\Commands;

use App\Models\Resource;
use App\Services\ResourceCatalogQuery;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class PerformanceBenchmarkCommand extends Command
{
    protected $signature = 'agora:performance-benchmark
                            {--output= : Path for JSON report}
                            {--samples=5 : Timing samples per endpoint}';

    protected $description = 'Index API/database performance and write a baseline report';

    public function handle(ResourceCatalogQuery $catalog): int
    {
        $samples = max(1, (int) $this->option('samples'));
        $output = $this->option('output') ?: config('agora.performance.benchmark_output');

        $resourceCount = Resource::query()->where('status', 'published')->count();
        $driver = DB::connection()->getDriverName();

        $report = [
            'generated_at' => now()->toIso8601String(),
            'environment' => [
                'app_env' => config('app.env'),
                'database_driver' => $driver,
                'cache_store' => config('cache.default'),
                'queue_connection' => config('queue.default'),
            ],
            'dataset' => [
                'published_resources' => $resourceCount,
                'resource_files' => DB::table('resource_files')->count(),
            ],
            'timings_ms' => [
                'catalog_query' => $this->benchmarkCatalogQuery($catalog, $samples),
                'catalog_count' => $this->benchmark(fn () => Resource::query()->where('status', 'published')->count(), $samples),
                'route_list' => $this->benchmark(fn () => count(Route::getRoutes()), 1),
            ],
            'indexes' => $this->indexInventory($driver),
            'capacity_notes' => $this->capacityNotes($resourceCount),
        ];

        File::ensureDirectoryExists(dirname($output));
        File::put($output, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $this->info("Performance baseline written to {$output}");
        $this->table(
            ['Metric', 'p50 (ms)', 'p95 (ms)'],
            collect($report['timings_ms'])->map(fn ($row, $key) => [
                $key,
                $row['p50'],
                $row['p95'],
            ])->values()->all(),
        );

        return self::SUCCESS;
    }

    /**
     * @return array{p50: float, p95: float, min: float, max: float}
     */
    private function benchmarkCatalogQuery(ResourceCatalogQuery $catalog, int $samples): array
    {
        $request = Request::create('/api/v1/resources', 'GET', [
            'per_page' => 24,
        ]);

        return $this->benchmark(function () use ($catalog, $request) {
            $catalog->published($request)->limit(24)->get();
        }, $samples);
    }

    /**
     * @return array{p50: float, p95: float, min: float, max: float}
     */
    private function benchmark(callable $callback, int $samples): array
    {
        $durations = [];

        for ($i = 0; $i < $samples; $i++) {
            $start = hrtime(true);
            $callback();
            $durations[] = (hrtime(true) - $start) / 1_000_000;
        }

        sort($durations);

        return [
            'min' => round($durations[0], 2),
            'p50' => round($durations[(int) floor(count($durations) * 0.5)], 2),
            'p95' => round($durations[(int) floor(count($durations) * 0.95)], 2),
            'max' => round($durations[array_key_last($durations)], 2),
        ];
    }

    /**
     * @return list<string>
     */
    private function indexInventory(string $driver): array
    {
        if ($driver !== 'pgsql') {
            return ['Index inventory available on PostgreSQL only.'];
        }

        $rows = DB::select("
            SELECT indexname
            FROM pg_indexes
            WHERE schemaname = 'public'
              AND tablename IN ('resources', 'resource_files', 'resource_views', 'downloads')
            ORDER BY indexname
        ");

        return array_map(fn ($row) => $row->indexname, $rows);
    }

    /**
     * @return list<string>
     */
    private function capacityNotes(int $resourceCount): array
    {
        $notes = [
            'Use cursor pagination (?cursor=) for deep catalogs over 100k resources.',
            'Run agora:simulate-load before releases to validate latency under concurrency.',
            'Run queue workers for RecordResourceView jobs in production.',
            'Serve downloads from object storage or nginx X-Accel-Redirect at scale.',
        ];

        if ($resourceCount < 1000) {
            $notes[] = 'Dataset is small; run agora:performance-scale --count=100000 on staging for realistic benchmarks.';
        }

        return $notes;
    }
}
