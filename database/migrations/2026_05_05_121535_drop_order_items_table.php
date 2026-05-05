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
        Schema::dropIfExists('order_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('price_plan_id')->nullable()->constrained('price_plans')->nullOnDelete();
            $table->integer('quantity')->default(0);
            $table->string('name')->nullable();
            $table->text('data')->nullable();
            $table->decimal('price', 12, 4)->default(0);
            $table->decimal('discount', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->decimal('total_tax', 12, 4)->default(0);
            $table->decimal('total_gross', 12, 4)->default(0);
            $table->decimal('tax', 12, 4)->default(0);
            $table->string('product_code')->nullable();
            $table->integer('contract_length')->default(1);
            $table->integer('group')->default(0);
            $table->timestamps();
        });
    }
};
