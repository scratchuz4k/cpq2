<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\SpaRulesEnum;
use App\Models\SpaRule;
use Illuminate\Database\Seeder;

class SpaRulesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (SpaRulesEnum::cases() as $case) {
            SpaRule::updateOrCreate(['code' => $case->name], ['name' => $case->getName(), 'class' => $case->value, ...$case->getTasks()]);
        }

    }
}
