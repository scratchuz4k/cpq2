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
        Schema::table('prices', function (Blueprint $table) {
            $table->string('code')->after('id');
            $table->decimal('tax_rate', 12, 2)->default(0)->change();
            $table->decimal('net', 12, 2)->default(0)->change();
            $table->decimal('tax', 12, 2)->default(0)->change();
            $table->decimal('gross', 12, 2)->default(0)->change();
            $table->decimal('net_monthly', 12, 2)->default(0)->after('gross');;
            $table->decimal('tax_monthly', 12, 2)->default(0)->after('net_monthly');;
            $table->decimal('gross_monthly', 12, 2)->default(0)->after('tax_monthly');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->bigInteger('tax_rate')->change();
            $table->bigInteger('net')->change();
            $table->bigInteger('tax')->change();
            $table->bigInteger('gross')->change();
            $table->dropColumn([
                'code',
                'net_monthly',
                'tax_monthly',
                'gross_monthly',
            ]);
        });
    }
};
