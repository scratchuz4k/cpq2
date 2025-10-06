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
        Schema::connection('mysql_shopblocks')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('code');
            $table->text('slug');
            $table->text('name')->nullable();
            $table->longText('description')->nullable();
            $table->integer('stock')->default(0);
            $table->text('primary_image')->nullable();
            $table->json('images');
            $table->json('price');
            $table->integer('price_plan_id');
            $table->foreignId('brand_id')->nullable()->constrained('brands', 'id')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained('models', 'id')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories', 'id')->nullOnDelete();
            $table->string('updated')->nullable();
            $table->datetime('expire_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_shopblocks')->dropIfExists('products');
    }
};
