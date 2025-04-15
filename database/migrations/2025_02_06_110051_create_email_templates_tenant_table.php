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
        Schema::create('email_templates_tenant', function (Blueprint $table) {
            $table->id();
            $table->string('to')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('subject');
            $table->longText('body');
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('email_template_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates_tenant');
    }
};
