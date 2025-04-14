<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\EmailTemplate\QuoteAccountUser;
use Database\Seeders\EmailTemplate\UserRecoveryPassword;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserRecoveryPassword::class,
            QuoteAccountUser::class
        ]);
    }
}
