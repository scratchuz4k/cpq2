<?php

namespace Database\Seeders;

use Database\Seeders\EmailTemplate\QuoteAccountUser;
use Database\Seeders\EmailTemplate\QuoteApprovalClientReview;
use Database\Seeders\EmailTemplate\UserRecoveryPassword;
use Illuminate\Database\Seeder;

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
            QuoteAccountUser::class,
            QuoteApprovalClientReview::class,
        ]);
    }
}
