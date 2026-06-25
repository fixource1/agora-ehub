<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_resource', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('author');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['author_id', 'resource_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_resource');
    }
};
