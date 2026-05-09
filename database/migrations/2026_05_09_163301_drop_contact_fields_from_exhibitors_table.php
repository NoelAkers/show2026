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
        Schema::table('exhibitors', function (Blueprint $table) {
            $table->dropColumn(['email', 'phone', 'address']);
        });
    }

    public function down(): void
    {
        Schema::table('exhibitors', function (Blueprint $table) {
            $table->string('email')->nullable()->after('sort_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('address')->nullable()->after('phone');
        });
    }
};
