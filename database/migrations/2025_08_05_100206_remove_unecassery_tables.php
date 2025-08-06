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
        Schema::table('product_category', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('solution_type_id')->nullable()->constrained('solution_types', 'id')->nullOnDelete();
            $table->integer('sort_order')->default(0);
        });

        $items = DB::table('product_category')->get();
        foreach ($items as $item) {

            $temp = DB::table('categories')
                ->where('id', $item->category_id)->first();

            if (! $temp) {
                continue;
            }
            DB::table('products')
                ->where('id', $item->product_id)
                ->update(['category_id' => $item->category_id]);
        }

        $items = DB::table(table: 'categories_solution_type')->get();
        foreach ($items as $item) {
            DB::table('categories')
                ->where('id', $item->category_id)
                ->update(['solution_type_id' => $item->solution_type_id]);
        }

        Schema::dropIfExists('product_category');
        Schema::dropIfExists('categories_solution_type');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['solution_type_id']);
            $table->dropColumn('solution_type_id');
            $table->dropColumn('sort_order');
        });

        Schema::create('categories_solution_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(1);
            $table->timestamps();
        });

        Schema::create('product_category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }
};
