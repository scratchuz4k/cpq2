<?php

use App\Enums\ImportsEnum;
use App\Enums\ImportStatusesEnum;
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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('model', ImportsEnum::names())->default(ImportsEnum::User->name);
            $table->foreignId('file_id')->nullable()->constrained('resources', 'id')->nullOnDelete();
            $table->enum('status', ImportStatusesEnum::names())->default(ImportStatusesEnum::IN_PROGRESS->name);
            $table->json('data')->nullable();
            $table->boolean('overwrite')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
