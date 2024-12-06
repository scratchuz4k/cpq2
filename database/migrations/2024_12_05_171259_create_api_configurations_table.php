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
            $table->string('name')->nullable();
            $table->string('client_id')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('access_token')->nullable();
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
