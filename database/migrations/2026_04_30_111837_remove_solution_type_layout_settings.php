<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('configurations')
            ->where('configurable_type', 'App\\Models\\SolutionType')
            ->whereIn('key', ['calculator_layout', 'component', 'has_custom_table'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
