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
        Schema::table('show_classes', function (Blueprint $table) {
            $table->foreignId('prize_level_id')->nullable()->constrained()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('show_classes', function (Blueprint $table) {
            $table->dropForeign(['prize_level_id']);
            $table->dropColumn('prize_level_id');
        });
    }
};
