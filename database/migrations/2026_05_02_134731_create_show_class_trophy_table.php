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
        Schema::create('show_class_trophy', function (Blueprint $table) {
            $table->foreignId('trophy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('show_class_id')->constrained()->cascadeOnDelete();
            $table->primary(['trophy_id', 'show_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('show_class_trophy');
    }
};
