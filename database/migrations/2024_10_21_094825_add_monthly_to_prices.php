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
            $table->double('tax_rate')->default(0)->change();
            $table->double('net')->default(0)->change();
            $table->double('tax')->default(0)->change();
            $table->double('gross')->default(0)->change();
            $table->double('net_monthly')->default(0)->after('gross');;
            $table->double('tax_monthly')->default(0)->after('net_monthly');;
            $table->double('gross_monthly')->default(0)->after('tax_monthly');;
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
