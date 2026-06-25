<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('disk')->default('local');
            $table->string('mime_type')->nullable();
            $table->string('file_type');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('version')->default('1.0');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_downloadable')->default(true);
            $table->unsignedInteger('download_count')->default(0);
            $table->string('checksum')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_files');
    }
};
