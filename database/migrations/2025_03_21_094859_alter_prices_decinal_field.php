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
            $table->decimal('tax_rate', 12, 4)->default(0)->change();
            $table->decimal('net', 12, 4)->default(0)->change();
            $table->decimal('tax', 12, 4)->default(0)->change();
            $table->decimal('gross', 12, 4)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('tax_rate', 12, 2)->default(0)->change();
            $table->decimal('net', 12, 2)->default(0)->change();
            $table->decimal('tax', 12, 2)->default(0)->change();
            $table->decimal('gross', 12, 2)->default(0)->change();
        });
    }
};
