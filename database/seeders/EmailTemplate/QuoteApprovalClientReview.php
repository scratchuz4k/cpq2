<?php

namespace Database\Seeders\EmailTemplate;

use App\Enums\EmailTemplatesEnum;
use App\Models\EmailTemplate;
use App\Models\EmailToken;
use Illuminate\Database\Seeder;

class QuoteApprovalClientReview extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // PASSWORD RESET - TOKEN
        $email = EmailTemplate::updateOrCreate(
            ['code' => EmailTemplatesEnum::QUOTE_CLIENT_REVIEW->name],
            [
                'description' => EmailTemplatesEnum::QUOTE_CLIENT_REVIEW->value,
                'subject' => 'Your quote #[quote_number] for [account_name] has been sent for review',
                'body' => '<p>Dear [account_name],</p><p>Please follow the <a href="[quote_link]">link</a> for the quotation #[quote_number] as to start the review process. </p><p>To see the quote, please use this password: [quote_password],</p><p>If any adjustments are needed, or if you have any questions, please feel free to reach out. We are happy to assist in any way.</p><p>Best regards.</p>',
                'notes' => 'Sent to account email associated in the quote.',
            ],
        );

        EmailToken::updateOrCreate(
            [
                'field' => 'account_name',
                'email_template_id' => $email->id,
            ],
            [
                'description' => 'Account Name',
            ],
        );

        EmailToken::updateOrCreate(
            [
                'field' => 'quote_number',
                'email_template_id' => $email->id,
            ],
            [
                'description' => 'Quote Number',
            ],
        );

        EmailToken::updateOrCreate(
            [
                'field' => 'quote_link',
                'email_template_id' => $email->id,
            ],
            [
                'description' => 'Quote Link',
            ],
        );

        EmailToken::updateOrCreate(
            [
                'field' => 'quote_password',
                'email_template_id' => $email->id,
            ],
            [
                'description' => 'Password to open Quote Link',
            ],
        );
    }
}
