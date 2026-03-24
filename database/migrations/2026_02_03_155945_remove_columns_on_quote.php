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
        Schema::table('quotes', callback: function (Blueprint $table) {
            $table->dropColumn(['total_tax', 'total_gross']);
            $table->decimal('total_discount', 12, 4)->default(0)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('total_tax', 12, 4)->default(0);
            $table->decimal('total_gross', 12, 4)->default(0);
            $table->dropColumn(['total_discount']);
        });
    }
};
