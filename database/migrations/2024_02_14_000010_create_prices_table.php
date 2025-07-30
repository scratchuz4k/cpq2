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

        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spa_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('tax_rate');
            $table->bigInteger('net');
            $table->bigInteger('tax');
            $table->bigInteger('gross');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('prices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('spa_id');
            $table->dropConstrainedForeignId('product_id');
        });

        Schema::dropIfExists('prices');
    }
};
