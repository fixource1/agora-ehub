<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_files', function (Blueprint $table) {
            $table->index(['resource_id', 'is_primary'], 'resource_files_resource_primary_idx');
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->index('uploader_id', 'resources_uploader_id_idx');
            $table->index(['status', 'resource_type_id', 'published_at'], 'resources_status_type_published_idx');
            $table->index(['status', 'category_id', 'published_at'], 'resources_status_category_published_idx');
        });

        Schema::table('author_resource', function (Blueprint $table) {
            $table->index('resource_id', 'author_resource_resource_id_idx');
        });

        if (Schema::hasTable('downloads')) {
            Schema::table('downloads', function (Blueprint $table) {
                $table->index(['device_id', 'downloaded_at'], 'downloads_device_downloaded_idx');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS resources_published_active_idx ON resources (published_at DESC, id DESC) WHERE deleted_at IS NULL AND status = \'published\'');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS resources_published_active_idx');
        }

        if (Schema::hasTable('downloads')) {
            Schema::table('downloads', function (Blueprint $table) {
                $table->dropIndex('downloads_device_downloaded_idx');
            });
        }

        Schema::table('author_resource', function (Blueprint $table) {
            $table->dropIndex('author_resource_resource_id_idx');
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->dropIndex('resources_uploader_id_idx');
            $table->dropIndex('resources_status_type_published_idx');
            $table->dropIndex('resources_status_category_published_idx');
        });

        Schema::table('resource_files', function (Blueprint $table) {
            $table->dropIndex('resource_files_resource_primary_idx');
        });
    }
};
