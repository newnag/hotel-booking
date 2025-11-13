<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'line_user_id')) {
                $table->string('line_user_id')->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'line_linked_at')) {
                $table->timestamp('line_linked_at')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['line_user_id', 'line_linked_at']);
        });
    }
};
