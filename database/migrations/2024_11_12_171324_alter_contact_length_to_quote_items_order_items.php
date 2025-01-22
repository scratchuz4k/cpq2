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
            $table->integer('contract_length')->default(1);
            $table->integer('quantity')->default(0)->change();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('contract_length')->default(1);
            $table->integer('quantity')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('contract_length');
            $table->decimal('quantity',12,2)->default(0)->change();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('contract_length');
            $table->decimal('quantity',12,2)->default(0)->change();
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('contract_length', 4, 2)->default(1);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('contract_length', 4, 2)->default(1);
        });
    }
};
