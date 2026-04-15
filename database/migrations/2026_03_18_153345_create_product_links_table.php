<?php

use App\Models\Product;
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
        Schema::create('product_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products', 'id')->cascadeOnDelete();
            $table->foreignId('child_id')->constrained('products', 'id')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'child_id']);
        });

        $procuts = Product::query()->whereNotNull('parent_id')->get();

        foreach ($procuts as $product) {
            DB::table('product_links')->insert([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->parent_id,
                'child_id' => $product->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('products', 'id')->nullOnDelete()->after('tenant_id');
        });

        Schema::dropIfExists('product_links');
    }
};
