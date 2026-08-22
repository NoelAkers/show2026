<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('exhibitors')
            ->where('amount_paid_pence', '!=', 0)
            ->select('id', 'amount_paid_pence')
            ->orderBy('id')
            ->each(function (object $exhibitor): void {
                DB::table('transactions')->insert([
                    'exhibitor_id' => $exhibitor->id,
                    'amount_pence' => abs($exhibitor->amount_paid_pence),
                    'type' => $exhibitor->amount_paid_pence > 0 ? 'cash_receipt' : 'cash_payment',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('exhibitors', function (Blueprint $table) {
            $table->dropColumn(['has_paid', 'amount_paid_pence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exhibitors', function (Blueprint $table) {
            $table->boolean('has_paid')->default(false);
            $table->integer('amount_paid_pence')->default(0);
        });
    }
};
