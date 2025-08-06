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
        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->default(0);
            $table->dropColumn('tax_rate');
            $table->dropColumn('tax');
            $table->dropColumn('gross');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('cost');
            $table->decimal('tax_rate', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('gross', 12, 2)->default(0);
        });
    }
};
