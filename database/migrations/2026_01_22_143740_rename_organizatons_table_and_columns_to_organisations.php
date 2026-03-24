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
        Schema::table('organization_logs', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::rename('organizations', 'organisations');
        Schema::rename('organization_logs', 'organisation_logs');

        Schema::table('organisation_logs', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'organisation_id');
        });

        Schema::table('organisation_logs', function (Blueprint $table) {
            $table->foreign('organisation_id')
                ->references('id')
                ->on('organisations')
                ->cascadeOnDelete();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_logs', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('organisation_logs', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'organization_id');
        });

        Schema::rename('organisations', 'organizations');
        Schema::rename('organisation_logs', 'organization_logs');

        Schema::table('organization_logs', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }
};
