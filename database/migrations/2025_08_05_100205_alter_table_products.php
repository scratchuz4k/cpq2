<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('parent_id')->after('tenant_id')->nullable()->constrained('products', 'id')->cascadeOnDelete();
            $table->foreignId('category_id')->after('parent_id')->nullable()->constrained('categories', 'id')->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->renameColumn('code', 'sku');
        });

        $items = DB::table('products')->get();
        foreach ($items as $item) {
            $status = $item->is_active ? 'Active' : 'Inactive';
            $date = Carbon::parse($item->expires_at);
            if ($item->expires_at && $date->isPast()) {
                $status = 'Retired';
            }

            DB::table('products')
                ->where('id', $item->id)
                ->update(['status' => $status]);
        }

        Schema::table('product_category', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::dropIfExists('product_category');
        Schema::dropIfExists('categories_solution_type');

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('solution_type_id')->nullable()->constrained('solution_type_tenant', 'id')->nullOnDelete();
            $table->integer('sort_order')->default(0);
        });
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

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->dropColumn('status');
            $table->dropColumn('expires_at');
            $table->renameColumn('sku', 'code');
        });
    }
};
