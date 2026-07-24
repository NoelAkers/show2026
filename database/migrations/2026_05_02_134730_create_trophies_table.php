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
        Schema::create('trophies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_points_based')->default(true);
            $table->foreignId('judge_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('steward_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('winning_entry_id')->nullable()->constrained('entries')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trophies');
    }
};
