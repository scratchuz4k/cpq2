<?php

use App\Enums\QuoteStatusesEnum;
use App\Models\Activity;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $expiredStatus = QuoteStatusesEnum::EXPIRED->name;
        $systemUser = User::where('email', 'system@cpq.cloudmarket.com')->first();
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $configs = $tenant->configs ?? [];
            if (! array_key_exists('log_system_is_active', $configs) || $configs['log_system_is_active'] == '0') {
                continue;
            }

            DB::table('quotes')
                ->where('quotes.status', $expiredStatus)
                ->whereNull('quotes.deleted_at')
                ->whereNotExists(function ($q) use ($expiredStatus) {
                    $q->select(DB::raw(1))
                        ->from('activities')
                        ->whereColumn('activities.activitable_id', 'quotes.id')
                        ->where('activities.activitable_type', 'App\\Models\\Quote')
                        ->where('activities.operation', 'updated')
                        ->whereJsonContains('activities.data', [
                            'field' => 'status',
                            'to' => $expiredStatus,
                        ]);
                })
                ->orderBy('quotes.id')
                ->chunkById(100, function ($quotes) use ($expiredStatus, $systemUser) {
                    $rows = [];

                    foreach ($quotes as $quote) {
                        $lastStatusActivity = Activity::query()
                            ->where('activitable_type', Quote::class)
                            ->where('activitable_id', $quote->id)
                            ->where('operation', ['updated', 'created'])
                            ->whereJsonContains('data', ['field' => 'status'])
                            ->latest()
                            ->first();

                        $statusTo = null;

                        if ($lastStatusActivity) {
                            $change = collect($lastStatusActivity->data)
                                ->first(function ($item) {
                                    return (is_array($item) ? ($item['field'] ?? null) : ($item->field ?? null)) === 'status';
                                });

                            $statusTo = is_array($change) ? ($change['to'] ?? null) : ($change->to ?? null);
                        }

                        $data = [
                            [
                                'field' => 'status',
                                'from' => isset($statusTo) ? $statusTo : QuoteStatusesEnum::QUOTE->name,
                                'to' => $expiredStatus,
                            ],
                        ];

                        $rows[] = [
                            'operation' => 'updated',
                            'data' => json_encode($data),
                            'activitable_id' => $quote->id,
                            'activitable_type' => 'App\\Models\\Quote',
                            'user_id' => $systemUser->id,
                            'tenant_id' => $quote->tenant_id,
                            'created_at' => $quote->updated_at ?? now(),
                            'updated_at' => $quote->updated_at ?? now(),
                        ];
                    }

                    if (! empty($rows)) {
                        DB::table('activities')->insert($rows);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $expiredStatus = QuoteStatusesEnum::EXPIRED->name;

        DB::table('activities')
            ->where('activitable_type', 'App\\Models\\Quote')
            ->where('operation', 'updated')
            ->whereJsonContains('data', [
                'field' => 'status',
                'to' => $expiredStatus,
            ])
            ->delete();
    }
};
