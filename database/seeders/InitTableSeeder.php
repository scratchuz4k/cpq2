<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Tenant;
use App\Models\SpaRule;
use App\Models\Supplier;
use App\Models\PricePlan;
use App\Enums\SpaRulesEnum;
use App\Enums\PricePlanEnum;
use App\Models\SolutionType;
use App\Models\SpaOperation;
use Illuminate\Database\Seeder;
use App\Enums\SpaOperationsEnum;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

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
            "name" => "CloudMarket"
        ], [
            "domain" => "localhost",
            "database" => "cloudmarket"
        ]);

        $super_user = User::updateOrCreate([
            'email' => 'super@example.com',
        ], [
            'name' => 'Super User',
            'tenant_id' => $tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $super_user->tenants()->sync([$super_user->id]);

        SolutionType::updateOrCreate([
            "code" => 3
        ], [
            "name" => "Connectivity - Ethernet",
        ]);
        SolutionType::updateOrCreate([
            "code" => 4
        ], [
            "name" => "Mobile",
        ]);
        SolutionType::updateOrCreate([
            "code" => 5
        ], [
            "name" => "Hardware",
        ]);

        foreach (SpaRulesEnum::cases() as $case) {
            SpaRule::updateOrCreate(['code' => $case->name], ['class' => $case->value, ...$case->getTasks()]);
        }

        foreach (SpaOperationsEnum::cases() as $case) {
            SpaOperation::updateOrCreate(['code' => $case->name], ['class' => $case->value]);
        }

        foreach (PricePlanEnum::cases() as $case) {
            PricePlan::updateOrCreate(['code' => $case->name], ['name' => $case->value]);
        }

    }
}
