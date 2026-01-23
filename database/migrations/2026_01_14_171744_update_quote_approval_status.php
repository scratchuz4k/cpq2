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
        Schema::table('quote_approvals', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->change();
        });
        DB::table('quote_approvals')
            ->where('status', 'pending')
            ->update(['status' => 'PENDING']);

        DB::table('quote_approvals')
            ->where('status', 'approved')
            ->update(['status' => 'APPROVED']);

        DB::table('quote_approvals')
            ->where('status', 'rejected')
            ->update(['status' => 'REJECTED']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_approvals', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
        DB::table('quote_approvals')
            ->where('status', 'PENDING')
            ->update(['status' => 'pending']);

        DB::table('quote_approvals')
            ->where('status', 'APPROVED')
            ->update(['status' => 'approved']);

        DB::table('quote_approvals')
            ->where('status', 'REJECTED')
            ->update(['status' => 'rejected']);
    }
};
