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

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('reference')->nullable();
            $table->string('primary_contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('address_1')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->timestamps(6);
            $table->softDeletes();

            $table->unique(['tenant_id', 'reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('accounts', function (Blueprint $table) {
        //     $table->dropConstrainedForeignId('tenant_id');
        //     $table->dropConstrainedForeignId('region_id');
        // });

        Schema::dropIfExists('accounts');
    }
};
