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
        Schema::connection('mysql_shopblocks')->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->text('name');
            $table->integer('children_count')->default(0);
            $table->boolean('active')->default(true);
            $table->json('image')->nullable();
            $table->longText('long_description')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('order_num')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories', 'id')->nullOnDelete();
            $table->text('path')->nullable();
            $table->text('short_description')->nullable();
            $table->boolean('show_products')->default(true);
            $table->text('slug')->nullable();
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_shopblocks')->dropIfExists('brands');
    }
};
