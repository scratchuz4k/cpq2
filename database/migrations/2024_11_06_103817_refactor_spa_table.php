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
        Schema::table('spas', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['merchant_id']);
            $table->dropForeign(['account_id']);
            $table->dropColumn(['name', 'data',  'supplier_id', 'merchant_id', 'account_id']);

            // First polymorphic relationship
            $table->unsignedBigInteger('rule_type')->after('id');
            $table->unsignedBigInteger('field_1')->after('rule_type');
            $table->unsignedBigInteger('field_2')->nullable()->after('field_1');
            $table->unsignedBigInteger('operation')->after('field_2');
            $table->decimal('value', 12, 4)->after('operation')->default(0);
            $table->decimal('percentage', 12, 4)->after('value')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spas', function (Blueprint $table) {
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name', 100)->nullable();
            $table->text('data')->nullable();
            $table->dropColumn(['field_1', 'field_2', 'rule_type', 'operation', 'discount_value', 'discount_percentage']);
        });
    }
};
