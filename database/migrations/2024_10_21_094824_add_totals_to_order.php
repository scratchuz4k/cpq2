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
        Schema::table('orders', function (Blueprint $table) {
            $table->double('margin')->default(0);
            $table->double('discount')->default(0);
            $table->double('total')->default(0);
            $table->double('total_tax')->default(0);
            $table->double('total_gross')->default(0);
            $table->double('total_monthly')->default(0);
            $table->double('total_monthly_tax')->default(0);
            $table->double('total_monthly_gross')->default(0);
            $table->double('comission')->default(0);
            $table->double('comission_tax')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'total',
                'total_monthly',
                'margin',
                'discount',
            ]);
        });
    }
};
