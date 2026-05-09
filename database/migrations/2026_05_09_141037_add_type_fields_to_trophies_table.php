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
        Schema::table('trophies', function (Blueprint $table) {
            $table->boolean('is_points_based')->default(true)->after('description');
            $table->foreignId('judge_id')->nullable()->constrained('users')->nullOnDelete()->after('is_points_based');
            $table->foreignId('winning_entry_id')->nullable()->constrained('entries')->nullOnDelete()->after('judge_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trophies', function (Blueprint $table) {
            $table->dropForeign(['winning_entry_id']);
            $table->dropForeign(['judge_id']);
            $table->dropColumn(['winning_entry_id', 'judge_id', 'is_points_based']);
        });
    }
};
