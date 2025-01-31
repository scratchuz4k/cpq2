<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Enums\EmailTemplatesEnum;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Generate a dynamic email message, based on a template and parameters.
 * Replaces the template # tags # with the parameters values.
 * The email is sent to the specified address, with the specified CC and BCC addresses.
 * The email is logged in the email_history table, unless the isPreview flag is set to true.
 *
 * The email can contain attachments (from a disk), which are added to the email message.
 *
 * The isPreview flag is used to prevent the email from being sent, and to avoid logging it in the email_history table.
 * This is used on the templates editor page, where the user can preview the email before sending it.
 *
 * @param  Tenant  $tenant  (the user id of the user who sent the email)
 * @param  EmailTemplatesEnum  $template  (the email template code)
 * @param  array  $parameters  (key-value pairs)
 * @param  array  $files  (array of files to attach to the email)
 * @param  array  $overrides  (array of recipient, cc, bcc and subject overrides)
 * @param  bool  $isPreview  (if true, the email is not sent and not logged in the email_history table)
 * @param  User  $user  (the user id of the user who sent the email)
 * @return Mailable
 */
class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    private ?User $user;
    private Tenant $tenant;
    private array $files;
    private string $body;
    private string $logo;
    private bool $isPreview;
    private array $overrides;
    private array $parameters;
    private ?string $footer;
    private ?EmailTemplate $template;

    public function __construct(
        Tenant $tenant,
        EmailTemplatesEnum $template,
        array $parameters = [],
        array $files = [],
        array $overrides = [],
        bool $isPreview = false,
        ?User $user = null,
    ) {
        $this->files = $files;
        $this->isPreview = $isPreview;
        $this->parameters = $parameters;
        $this->overrides = $overrides;
        $this->template = EmailTemplate::where('code', $template->name)->first() ?? null;
        $this->user = $user;
        $this->tenant = $tenant;

        $this->onQueue('emails');
    }

    public function build()
    {
        if (is_null($this->template) || $this->template == '') {
            throw new \Exception('Dynamic Email must contain a valid template code (got ' . $this->template . ')');
        }

        $this->logo = url('/images/Cloud-Market-Logo-Black-32x32.svg');
        $this->body = $this->template->body;
        $this->footer = "";

        if (!$this->isPreview) {
            $this->setHeaders();

            $this->replaceBodyTags($this->body);
            $this->replaceSubjectTags($this->subject);
        }

        $email = $this
            ->from(config('mail.from.address'), 'Cloudmarket')
            ->markdown('emails.dynamic')
            ->with([
                'logo' => $this->logo,
                'body' => $this->body,
                'footer' => $this->footer,
                'tenant' => $this->tenant,
            ]);

        // add attachments to the email
        foreach ($this->files as $file) {
            $email->attachFromStorageDisk(
                $file['disk'],
                $file['path'],
                $file['title'] ?? Str::uuid()->toString(),
                ['mime' => $file['mimetype'] ?? 'application/octet-stream'],
            );
        }

        // all emails sent using this class will be logged in the email_history table
        // unless the isPreview flag is set to true
        if (!$this->isPreview && $this->user) {
            // event(
            //     new EmailSent(
            //         data: [
            //             'email_template_code' => $this->template->code,
            //             'from' => config('mail.from.address'),
            //             'to' => implode(',', $this->getRecipient('to')),
            //             'cc' => implode(',', $this->getRecipient('cc')),
            //             'bcc' => implode(',', $this->getRecipient('bcc')),
            //             'subject' => $this->subject,
            //             'body' => $this->body,
            //         ],
            //         userId: $this->userId,
            //     ),
            // );
        }

        return $email;
    }

    /**
     * Set the email headers.
     *
     * @return void
     */
    private function setHeaders()
    {
        $headers = [
            'to' => $this->getRecipient('to'),
            'cc' => $this->getRecipient('cc'),
            'bcc' => $this->getRecipient('bcc'),
            'subject' => $this->getSubject(),
        ];

        $this->to($headers['to']);
        $this->cc($headers['cc']);
        $this->bcc($headers['bcc']);
        $this->subject($headers['subject']);
    }

    /**
     * Get the email recipient.
     *
     * @return array
     */
    private function getRecipient(string $type)
    {
        $recipient = $this->overrides[$type] ?? $this->template->$type;

        if ($type == 'to' && is_null($recipient)) {
            $recipient =  ''; // Add tenant general email
        }

        return array_filter(explode(',', $recipient));
    }

    /**
     * Get the email subject.
     *
     * @return string
     */
    private function getSubject()
    {
        $subject = $this->overrides['subject'] ?? $this->template->subject;

        return config('app.name') . ' - ' . $subject;
    }

    /**
     * Replace the # tags # in the email body with the parameters values.
     */
    private function replaceBodyTags(string $body): void
    {
        $result = preg_match_all("/(?<!\d)#([^#]*)\#/", $body, $matches);

        if ($result) {
            foreach ($matches[0] as $match) {
                $body = str_replace(
                    $match,
                    $this->parameters[substr($match, 1, strlen($match) - 2)],
                    $body,
                );
            }
        }

        $this->body = $body;
    }

    /**
     * Replace the # tags # in the email subject with the parameters values.
     */
    private function replaceSubjectTags(string $subject): void
    {
        $result = preg_match_all("/(?<!\d)#([^#]*)\#/", $subject, $matches);

        if ($result) {
            foreach ($matches[0] as $match) {
                $subject = str_replace(
                    $match,
                    $this->parameters[substr($match, 1, strlen($match) - 2)],
                    $subject,
                );
            }
        }

        $this->subject = $subject;
    }
}
