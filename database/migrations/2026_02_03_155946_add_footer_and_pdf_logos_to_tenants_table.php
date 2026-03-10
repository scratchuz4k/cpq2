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
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('logo_alt_id')->nullable()->constrained('resources', 'id')->nullOnDelete();
            $table->foreignId('logo_docs_id')->nullable()->constrained('resources', 'id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('logo_alt_id');
            $table->dropConstrainedForeignId('logo_docs_id');
        });
    }
};
