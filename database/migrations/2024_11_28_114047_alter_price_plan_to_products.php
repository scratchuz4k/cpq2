<?php

use App\Enums\PricePlanEnum;
use App\Models\PricePlan;
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
        $pricePlanId = PricePlan::where('code', PricePlanEnum::SINGLE->name)->value('id') ?? 2;
        Schema::table('products', function (Blueprint $table) use ($pricePlanId) {
            $table->foreignId('price_plan_id')->default($pricePlanId)->nullable()->constrained()->nullOnDelete();
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->dropForeign(['price_plan_id']);
            $table->dropColumn(['price_plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['price_plan_id']);
            $table->dropColumn(['price_plan_id']);
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->foreignId('price_plan_id')->after('product_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
