<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('visibility')->default('private');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('collection_resource', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('added_at')->useCurrent();
            $table->primary(['collection_id', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_resource');
        Schema::dropIfExists('collections');
    }
};
