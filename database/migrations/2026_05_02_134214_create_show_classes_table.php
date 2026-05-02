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
        Schema::create('show_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('show_section_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->tinyInteger('max_entries_per_exhibitor')->default(5);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['show_section_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('show_classes');
    }
};
