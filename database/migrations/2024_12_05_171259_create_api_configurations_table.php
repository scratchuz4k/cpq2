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
        Schema::create('api_configurations', function (Blueprint $table) {
            $table->id();

            $table->string('auth_type');
            $table->string('code');
            $table->text('name')->nullable();
            $table->text('client_id')->nullable();
            $table->text('username')->nullable();
            $table->text('password')->nullable();
            $table->text('token')->nullable();
            $table->text('extra_data')->nullable();

            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unique(['code', 'tenant_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_configurations');
    }
};
