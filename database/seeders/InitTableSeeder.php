<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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

class InitTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::updateOrCreate([
            'name' => 'CloudMarket',
        ], [
            'domain' => 'example.com',
            'database' => 'cloudmarket',
        ]);

        $super_user = User::updateOrCreate([
            'email' => 'super@example.com',
        ], [
            'name' => 'Super User',
            'tenant_id' => $tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $super_user->tenants()->sync([$super_user->id]);

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
