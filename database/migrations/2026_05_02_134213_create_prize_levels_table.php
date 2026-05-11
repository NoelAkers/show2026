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
        Schema::create('prize_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('first_place_pence');
            $table->unsignedInteger('second_place_pence');
            $table->unsignedInteger('third_place_pence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_levels');
    }
};
