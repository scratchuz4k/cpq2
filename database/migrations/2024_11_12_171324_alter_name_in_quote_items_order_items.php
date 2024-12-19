<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('quote_items', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE `quote_items` SET `column_name` = LEFT(`name`, 100)");
        DB::statement("UPDATE `order_items` SET `column_name` = LEFT(`name`, 100)");

        Schema::table('quote_items', function (Blueprint $table) {
            $table->string('name',100)->nullable()->change();});
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('name',100)->nullable()->change();
        });
    }
};
