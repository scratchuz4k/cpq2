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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('solution_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100)->nullable();
            $table->string('status')->nullable();
            $table->string('payment_method', 200)->nullable();
            $table->string('address_line1', 200)->nullable();
            $table->string('address_line2', 200)->nullable();
            $table->string('address_city', 200)->nullable();
            $table->string('address_province', 200)->nullable();
            $table->string('address_postcode', 200)->nullable();
            $table->string('address_country', 2)->nullable();
            $table->string('address_position', 100)->nullable();
            $table->text('address_data')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('account_id');
            $table->dropConstrainedForeignId('user_id');
        });


        Schema::dropIfExists('quotes');
    }
};
