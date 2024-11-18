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

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('contract_length');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('contract_length');
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->decimal('contract_length', 4, 2)->default(1);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('contract_length', 4, 2)->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('contract_length');
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('contract_length');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('contract_length', 4, 2)->default(1);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('contract_length', 4, 2)->default(1);
        });
    }
};
