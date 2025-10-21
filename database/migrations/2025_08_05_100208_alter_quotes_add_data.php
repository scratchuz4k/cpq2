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
        $items = DB::table('quotes')->get();
        foreach ($items as $item) {
            DB::table('quotes')
                ->where('id', $item->id)
                ->update(['data' => '{}']);
        }

        Schema::table('quotes', function (Blueprint $table) {
            $table->json('data')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('quotes', function (Blueprint $table) {
            $table->text('data')->nullable()->change();
        });
    }
};
