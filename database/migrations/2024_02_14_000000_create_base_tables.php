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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 100);
            $table->string('reference', 100)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');
        });

        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 100);
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 100);
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('name', 100);
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');

            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->nullOnDelete();

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->nullOnDelete();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('merchant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 100);
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->nullOnDelete();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 100);
            $table->string('reference')->unique()->nullable();
            $table->string('primary_contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('address_1')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');

            $table->unique(['tenant_id', 'reference']);
            $table->foreign('region_id')
                ->references('id')
                ->on('regions')
                ->nullOnDelete();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 100);
            $table->integer('solution_type_id')->nullable();
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

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('name', 100)->nullable();
            $table->text('data')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });

        Schema::create('spas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('merchant_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 100)->nullable();
            $table->text('data')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants');

            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->cascadeOnDelete();

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->cascadeOnDelete();

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('spa_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->bigInteger('tax_rate');
            $table->bigInteger('net');
            $table->bigInteger('tax');
            $table->bigInteger('gross');
            $table->timestamps();

            $table->foreign('spa_id')
                ->references('id')
                ->on('spas')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });


        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('merchant_id')->nullable();

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('merchants');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('suppliers');
    }
};
