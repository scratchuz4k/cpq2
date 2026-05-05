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
        Schema::table('quote_items', function (Blueprint $table) {
            $table->decimal('total', 15, 4)->default(0)->change();
            $table->decimal('total_gross', 15, 4)->default(0)->change();
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('cost', 12, 4)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->decimal('total', 15, 2)->default(0)->change();
            $table->decimal('total_gross', 15, 2)->default(0)->change();
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->default(0)->change();
        });
    }
};
