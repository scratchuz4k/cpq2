<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('quotes')
            ->where('status', 'PENDING_APPROVAL')
            ->update(['status' => 'PENDING']);

        DB::table('quotes')
            ->where('status', 'VALIDATED')
            ->update(['status' => 'APPROVED']);

        DB::table('quotes')
            ->where('status', 'DECLINED')
            ->update(['status' => 'REJECTED']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('quotes')
            ->where('status', 'PENDING')
            ->update(['status' => 'PENDING_APPROVAL']);

        DB::table('quotes')
            ->where('status', 'APPROVED')
            ->update(['status' => 'VALIDATED']);

        DB::table('quotes')
            ->where('status', 'REJECTED')
            ->update(['status' => 'DECLINED']);
    }
};
