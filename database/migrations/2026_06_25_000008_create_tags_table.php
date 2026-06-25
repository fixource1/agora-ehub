<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('resource_tag', function (Blueprint $table) {
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['resource_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_tag');
        Schema::dropIfExists('tags');
    }
};
