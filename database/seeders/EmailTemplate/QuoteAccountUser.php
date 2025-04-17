<?php

namespace Database\Seeders\EmailTemplate;

use App\Models\EmailToken;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use App\Enums\EmailTemplatesEnum;

class QuoteAccountUser extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // PASSWORD RESET - TOKEN
        $quoteAccountShare = EmailTemplate::updateOrCreate(
            ['code' => EmailTemplatesEnum::SEND_QUOTE_TO_ACCOUNT->name],
            [
                'description' => EmailTemplatesEnum::SEND_QUOTE_TO_ACCOUNT->value,
                'subject' => 'Quotation for [account_name]  - [quote_number]',
                'body' => '<p>Dear [account_name],</p><p>Please find attached the quotation [quote_number] as requested. The attached PDF outlines the pricing, terms, and relevant details.</p><p>If any adjustments are needed, or if you have any questions, please feel free to reach out. We are happy to assist in any way.</p><p>Best regards.</p>',
                'notes' => 'Sent to account email associated in the quote with pdf.',
            ],
        );

        EmailToken::updateOrCreate(
            [
                'field' => 'account_name',
                'email_template_id' => $quoteAccountShare->id
            ],
            [
                'description' => 'Account Name'
            ],
        );

        EmailToken::updateOrCreate(
            [
                'field' => 'quote_number',
                'email_template_id' => $quoteAccountShare->id
            ],
            [
                'description' => 'Quote Number'
            ],
        );
    }
}
