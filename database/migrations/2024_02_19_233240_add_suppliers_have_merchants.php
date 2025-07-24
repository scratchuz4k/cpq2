<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_supplier', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('merchant_id');

            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onDelete('cascade');

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->onDelete('cascade');

            $table->primary(['merchant_id', 'supplier_id'], 'merchant_has_suppliers_supplier_id_merchant_id_primary');
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('merchant_supplier');
    }
};
