<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'system@cpq.cloudmarket.com')
            ->where('name', 'System Bot')
            ->update(['name' => 'System']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('email', 'system@cpq.cloudmarket.com')
            ->where('name', 'System')
            ->update(['name' => 'System Bot']);
    }
};
