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
        Schema::connection('mysql_shopblocks')->create('dimension_types', function (Blueprint $table) {
            $table->id();
            $table->text('group');
            $table->text('key');
            $table->text('label');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_shopblocks')->dropIfExists('products');
    }
};
