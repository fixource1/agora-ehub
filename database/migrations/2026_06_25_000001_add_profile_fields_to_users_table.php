<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('institution')->nullable()->after('avatar');
            $table->string('department')->nullable()->after('institution');
            $table->text('bio')->nullable()->after('department');
            $table->timestamp('last_active_at')->nullable()->after('bio');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['avatar', 'institution', 'department', 'bio', 'last_active_at']);
        });
    }
};
