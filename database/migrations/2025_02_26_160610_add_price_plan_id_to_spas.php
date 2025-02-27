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
        Schema::table('spas', function (Blueprint $table) {
            $table->foreignId('price_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->dropForeign(['rule_id']);
            $table->dropUnique(['rule_id', 'field_1', 'field_2']);
            $table->foreign('rule_id')->references('id')->on('spa_rules')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spas', function (Blueprint $table) {
            $table->dropForeign(['price_plan_id']);
            $table->dropColumn(['price_plan_id']);
            $table->unique(['rule_id', 'field_1', 'field_2']);
        });
    }
};
