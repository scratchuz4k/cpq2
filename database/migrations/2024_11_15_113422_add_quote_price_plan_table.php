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
        Schema::create('quote_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->morphs('discountable');
            $table->decimal('discount', 12, 4)->default(0);
            $table->timestamps();
        });
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['discount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_discounts');

        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('discount', 12, 4)->default(0);
        });
    }
};
