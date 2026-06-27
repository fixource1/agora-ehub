<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PerformanceScaleCommand extends Command
{
    protected $signature = 'agora:performance-scale
                            {--count=10000 : Number of published resources to create}
                            {--chunk=500 : Insert batch size}';

    protected $description = 'Seed synthetic published resources for staging load tests (not for production)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to run in production.');

            return self::FAILURE;
        }

        $count = max(1, (int) $this->option('count'));
        $chunk = max(100, (int) $this->option('chunk'));

        $user = User::query()->first() ?? User::factory()->create();
        $typeIds = ResourceType::query()->pluck('id')->all();
        $categoryIds = Category::query()->pluck('id')->all();

        if ($typeIds === []) {
            $this->error('Seed resource types first: php artisan db:seed --class=ResourceTypeSeeder');

            return self::FAILURE;
        }

        $existing = Resource::query()->where('status', 'published')->count();
        $this->info("Scaling from {$existing} to ".($existing + $count).' published resources…');

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $created = 0;

        while ($created < $count) {
            $batch = [];

            for ($i = 0; $i < $chunk && $created < $count; $i++, $created++) {
                $index = $existing + $created + 1;
                $title = "Scale Test Resource {$index}";
                $batch[] = [
                    'uploader_id' => $user->id,
                    'resource_type_id' => $typeIds[array_rand($typeIds)],
                    'category_id' => $categoryIds !== [] ? $categoryIds[array_rand($categoryIds)] : null,
                    'title' => $title,
                    'slug' => 'scale-test-'.$index.'-'.Str::lower(Str::random(6)),
                    'subtitle' => 'Synthetic benchmark record',
                    'description' => 'Generated for performance testing at scale.',
                    'status' => 'published',
                    'published_at' => now()->subMinutes($index),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Resource::query()->insert($batch);
            $bar->advance(count($batch));
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Done. Run agora:performance-benchmark and agora:simulate-load next.');

        return self::SUCCESS;
    }
}
