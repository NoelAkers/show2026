<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trophy_restrictions', function (Blueprint $table) {
            $table->foreignId('trophy_id')->constrained()->cascadeOnDelete();
            $table->string('restriction');
            $table->primary(['trophy_id', 'restriction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trophy_restrictions');
    }
};
