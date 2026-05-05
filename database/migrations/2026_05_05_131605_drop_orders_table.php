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
        Schema::dropIfExists('orders');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('solution_type_id')->nullable()->constrained('solution_types')->nullOnDelete();
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
            $table->decimal('margin', 12, 4)->default(0);
            $table->decimal('discount', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->decimal('total_tax', 12, 4)->default(0);
            $table->decimal('total_gross', 12, 4)->default(0);
            $table->decimal('commission_value', 12, 4)->default(0);
            $table->decimal('commission_rate', 12, 4)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
};
