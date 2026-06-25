<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_file_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('downloaded');
            $table->string('device_id')->nullable();
            $table->string('device_name')->nullable();
            $table->timestamp('downloaded_at')->useCurrent();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'resource_id']);
        });

        Schema::create('reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_file_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('current_page')->nullable();
            $table->unsignedSmallInteger('total_pages')->nullable();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'resource_id']);
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('page_number')->nullable();
            $table->text('excerpt')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->string('location');
            $table->text('highlighted_text');
            $table->string('color')->default('yellow');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('notable');
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_favorites', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['user_id', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('highlights');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('reading_progress');
        Schema::dropIfExists('downloads');
    }
};
