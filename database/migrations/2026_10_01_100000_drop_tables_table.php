<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Custom solution type tables are no longer supported.
     */
    public function up(): void
    {
        Schema::dropIfExists('tables');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->text('code');
            $table->text('group')->nullable();
            $table->text('group_name')->nullable();
            $table->json('columns')->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->morphs('tableable');
            $table->timestamps();
        });
    }
};
