<?php

namespace Database\Seeders;

use App\Enums\OrderStatusesEnum;
use App\Enums\PricePlanEnum;
use App\Enums\SolutionTypesEnum;
use App\Enums\SpaOperationsEnum;
use App\Enums\SpaRulesEnum;
use App\Models\OrderStatus;
use App\Models\PricePlan;
use App\Models\SolutionType;
use App\Models\SpaOperation;
use App\Models\SpaRule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $super_user_email = env('SUPERUSER_EMAIL', 'super@example.com');
        $super_user_password = env('SUPERUSER_PASSWORD', 'werwerwer');
        if (! $super_user = User::where('email', $super_user_email)->first()) {
            $super_user = User::updateOrCreate([
                'email' => $super_user_email,
            ], [
                'name' => 'Super User',
                'tenant_id' => null,
                'password' => Hash::make($super_user_password),
            ]);
        }

        if (! $system_user = User::where('email', 'system@cpq.cloudmarket.com')->first()) {
            $system_user = User::updateOrCreate([
                'email' => 'system@cpq.cloudmarket.com',
            ], [
                'name' => 'System Bot',
                'tenant_id' => null,
                'password' => Hash::make(Str::random(10)),
            ]);
        }

        $tenant = Tenant::first();
        if (! Tenant::count()) {
            $tenant = Tenant::updateOrCreate([
                'name' => 'CloudMarket',
            ], [
                'domain' => 'example.com',
                'database' => 'cloudmarket',
            ]);
        }

        $allTenants = Tenant::all();
        $super_user->tenants()->sync($allTenants->pluck('id'));
        $super_user->update(['tenant_id' => $tenant->id]);
        $system_user->tenants()->sync($allTenants->pluck('id'));
        $system_user->update(['tenant_id' => $tenant->id]);

        foreach (SolutionTypesEnum::cases() as $case) {
            SolutionType::updateOrCreate(['code' => $case->value], ['name' => $case->getName()]);
        }

        foreach (SpaRulesEnum::cases() as $case) {
            SpaRule::updateOrCreate(['code' => $case->name], ['name' => $case->getName(), 'class' => $case->value, ...$case->getTasks()]);
        }

        foreach (SpaOperationsEnum::cases() as $case) {
            SpaOperation::updateOrCreate(['code' => $case->name], ['class' => $case->value]);
        }

        foreach (PricePlanEnum::cases() as $case) {
            PricePlan::updateOrCreate(['code' => $case->name], ['name' => $case->value]);
        }

        foreach (OrderStatusesEnum::cases() as $status) {
            OrderStatus::updateOrCreate(['code' => $status->name], ['name' => $status->value]);
        }
    }
}
