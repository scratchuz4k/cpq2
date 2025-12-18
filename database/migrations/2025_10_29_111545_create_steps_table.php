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
        Schema::create('steps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
            $table->unique(['name', 'tenant_id', 'solution_type_id']);
            $table->integer('sort_order')->default(0);
            $table->foreignId('solution_type_id')->nullable()->constrained('solution_types', 'id')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->dropUnique(['name', 'tenant_id', 'solution_type_id']);
            $table->dropForeign(['tenant_id']);
        });
        Schema::dropIfExists('steps');
    }
};
