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
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('contract_term_value', 'renewal_terms_value');
            $table->renameColumn('contract_term_unit', 'renewal_terms_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('renewal_terms_value', 'contract_term_value');
            $table->renameColumn('renewal_terms_unit', 'contract_term_unit');
        });
    }
};
