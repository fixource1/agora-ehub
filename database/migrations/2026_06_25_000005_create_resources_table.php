<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('resource_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('audience_level')->default('general');
            $table->string('status')->default('draft');
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->fullText(['title', 'subtitle', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
