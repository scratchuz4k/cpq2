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
        Schema::connection('mysql_shopblocks')->create('product_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products', 'id')->cascadeOnDelete();
            $table->foreignId('dimension_id')->constrained('dimensions', 'id')->cascadeOnDelete();
            $table->foreignId('dimension_type_id')->constrained('dimension_types', 'id')->cascadeOnDelete();
            $table->unique(['dimension_type_id', 'product_id']);
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
