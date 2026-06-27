<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateResourceViewsCommand extends Command
{
    protected $signature = 'agora:aggregate-resource-views';

    protected $description = 'Roll up resource_views into resources.view_count';

    public function handle(): int
    {
        if (! DB::getSchemaBuilder()->hasTable('resource_views')) {
            $this->warn('resource_views table not found.');

            return self::SUCCESS;
        }

        $updated = DB::update('
            UPDATE resources
            SET view_count = aggregated.total_views
            FROM (
                SELECT resource_id, COUNT(*) AS total_views
                FROM resource_views
                GROUP BY resource_id
            ) AS aggregated
            WHERE resources.id = aggregated.resource_id
        ');

        $this->info("Synced view_count for {$updated} resources.");

        return self::SUCCESS;
    }
}
