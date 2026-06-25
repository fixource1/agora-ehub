<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->string('publisher')->nullable();
            $table->string('institution')->nullable();
            $table->string('department')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('isbn')->nullable();
            $table->string('doi')->nullable();
            $table->text('citation')->nullable();
            $table->string('research_field')->nullable();
            $table->string('subject_classification')->nullable();
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->unique('resource_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_metadata');
    }
};
