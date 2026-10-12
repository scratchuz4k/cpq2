<?php

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The DealMaker / Simple view selector becomes a quote_simple_view toggle;
     * tenants on the old "Simple" view keep it.
     */
    public function up(): void
    {
        $configurations = DB::table('configurations')
            ->where('configurable_type', Tenant::class)
            ->where('key', 'component')
            ->get();

        foreach ($configurations as $configuration) {
            DB::table('configurations')->updateOrInsert(
                [
                    'configurable_type' => Tenant::class,
                    'configurable_id' => $configuration->configurable_id,
                    'key' => 'quote_simple_view',
                ],
                [
                    'value' => $configuration->value === 'Simple' ? '1' : '0',
                    'tenant_id' => $configuration->tenant_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        DB::table('configurations')
            ->where('configurable_type', Tenant::class)
            ->where('key', 'component')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $configurations = DB::table('configurations')
            ->where('configurable_type', Tenant::class)
            ->where('key', 'quote_simple_view')
            ->get();

        foreach ($configurations as $configuration) {
            DB::table('configurations')->insert([
                'configurable_type' => Tenant::class,
                'configurable_id' => $configuration->configurable_id,
                'key' => 'component',
                'value' => $configuration->value === '1' ? 'Simple' : 'DealMaker',
                'tenant_id' => $configuration->tenant_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('configurations')
            ->where('configurable_type', Tenant::class)
            ->where('key', 'quote_simple_view')
            ->delete();
    }
};
