<?php

namespace Database\Seeders\EmailTemplate;

use App\Models\EmailToken;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use App\Enums\EmailTemplatesEnum;

class UserRecoveryPassword extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // PASSWORD RESET - TOKEN
        $passwordResetEmail = EmailTemplate::updateOrCreate(
            ['code' => EmailTemplatesEnum::USER_PASSWORD_RECOVERY->name],
            [
                'description' => EmailTemplatesEnum::USER_PASSWORD_RECOVERY->value,
                'subject' => 'Password  Recovery',
                'body' => '<p>Dear Sir/Madam,<p/><p>You received this email because you were asked to reset your password for your account.<p/><p>Click the button below to set a new password:<p/><p><a href="#reset_link#" target="_blank" rel="noopener noreferrer">Set new password</a><p/><p>If this was not you, please ignore this email.<p/>',
                'notes' => "Sent to user's email.",
            ],
        );

        EmailToken::updateOrCreate(
            [
                'field' => 'reset_link',
                'email_template_id' => $passwordResetEmail->id
            ],
            [
                'description' => 'Password recovery link'
            ],
        );
    }
}
