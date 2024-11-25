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
        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('contract_length', 12, 4)->default(12);
            $table->decimal('margin', 12, 4)->default(0);
            $table->decimal('discount', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->decimal('total_tax', 12, 4)->default(0);
            $table->decimal('total_gross', 12, 4)->default(0);
            $table->decimal('total_monthly', 12, 4)->default(0);
            $table->decimal('total_monthly_tax', 12, 4)->default(0);
            $table->decimal('total_monthly_gross', 12, 4)->default(0);
            $table->decimal('commission_value', 12, 4)->default(0);
            $table->decimal('commission_rate', 12, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'contract_length',
                'margin',
                'discount',
                'total',
                'total_tax',
                'total_gross',
                'total_monthly',
                'total_monthly_tax',
                'total_monthly_gross',
                'commission_value',
                'commission_rate',
            ]);
        });
    }
};
