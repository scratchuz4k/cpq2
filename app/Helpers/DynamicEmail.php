<?php

namespace App\Helpers;

use App\Enums\EmailTemplatesEnum;
use App\Models\EmailHistory;
use App\Models\EmailTemplate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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
 * @param  User  $user  (the user id of the user who sent the email)
 * @param  Tenant  $tenant  (the user id of the user who sent the email)
 * @param  EmailTemplatesEnum  $templateEnum  (the email template code)
 * @param  EmailTemplate  $email  (the email template code)
 * @param  array  $parameters  (key-value pairs)
 * @param  array  $files  (array of files to attach to the email)
 * @param  array  $overrides  (array of recipient, cc, bcc and subject overrides)
 * @param  bool  $isPreview  (if true, the email is not sent and not logged in the email_history table)
 * @return Mailable
 */
class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    private ?User $user;

    private Tenant $tenant;

    private EmailTemplatesEnum $templateEnum;

    private EmailTemplate $email;

    private array $parameters;

    private array $files;

    private bool $isPreview;

    private array $overrides;

    public function __construct(
        Tenant $tenant,
        EmailTemplatesEnum $templateEnum,
        array $parameters = [],
        array $files = [],
        array $overrides = [],
        bool $isPreview = false,
        ?User $user = null,
    ) {
        // TODO: EMAILS: remove the return once we get the credentials for email functionality
        return;
        $this->files = $files;
        $this->isPreview = $isPreview;
        $this->parameters = $parameters;
        $this->overrides = $overrides;
        $this->templateEnum = $templateEnum;
        $this->user = $user;
        $this->tenant = $tenant;
    }

    public function build()
    {

        $this->email = $this->getTemplate($this->templateEnum, $this->tenant);

        $content = [
            'from' => config('mail.from.address') ?? 'Cloudmarket',
            'to' => $this->getRecipient('to'),
            'cc' => $this->getRecipient('cc'),
            'bcc' => $this->getRecipient('bcc'),
            'subject' => $this->getSubject(),
            'logo' => url('/images/Cloud-Market-Logo-Black-32x32.svg'),
            'body' => $this->replaceBodyTags($this->email->body),
            'footer' => '',
            'tenant' => $this->tenant,
            'email_template_id' => $this->email->id,
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user?->id,
        ];

        $this->to($content['to']);
        $this->cc($content['cc']);
        $this->bcc($content['bcc']);
        $this->subject($content['subject']);

        $email = $this
            ->from($content['from'])
            ->markdown('emails.dynamic')
            ->with($content);

        // add attachments to the email
        foreach ($this->files as $file) {
            $email->attachFromStorageDisk(
                $file['disk'],
                $file['path'],
                $file['title'] ?? Str::uuid()->toString(),
                ['mime' => $file['mimetype'] ?? 'application/octet-stream'],
            );
        }

        EmailHistory::create($content);

        return $email;
    }

    private function getTemplate($template, $tenant)
    {
        $mail = EmailTemplate::where('code', $template->name)->first() ?? null;
        $tenant_mail = $mail->tenants()->wherePivot('tenant_id', $tenant->id)->first();
        if ($tenant_mail) {
            $mail->setAttribute('to', $tenant_mail->pivot->to);
            $mail->setAttribute('cc', $tenant_mail->pivot->cc);
            $mail->setAttribute('bcc', $tenant_mail->pivot->bcc);
            $mail->setAttribute('subject', $tenant_mail->pivot->subject);
            $mail->setAttribute('body', $tenant_mail->pivot->body);
        }

        return $mail;
    }

    /**
     * Get the email recipient.
     *
     * @return array
     */
    private function getRecipient(string $type)
    {
        $recipient = $this->overrides[$type] ?? $this->email->$type;

        if ($type == 'to' && is_null($recipient)) {
            $recipient = ''; // Add tenant general email
        }

        return $recipient;
    }

    /**
     * Get the email subject.
     *
     * @return string
     */
    private function getSubject()
    {
        $subject = $this->replaceSubjectTags($this->overrides['subject'] ?? $this->email->subject);

        return config('app.name').' - '.$subject;
    }

    /**
     * Replace the # tags # in the email body with the parameters values.
     */
    private function replaceBodyTags(string $body): string
    {
        $result = preg_match_all("/(?<!\d)\[([^\]]*)\]/", $body, $matches);

        if ($result) {
            foreach ($matches[0] as $match) {
                $body = str_replace(
                    $match,
                    $this->parameters[substr($match, 1, strlen($match) - 2)],
                    $body,
                );
            }
        }

        return $body;
    }

    /**
     * Replace the # tags # in the email subject with the parameters values.
     */
    private function replaceSubjectTags(string $subject): string
    {
        $result = preg_match_all("/(?<!\d)\[([^\]]*)\]/", $subject, $matches);

        if ($result) {
            foreach ($matches[0] as $match) {
                $subject = str_replace(
                    $match,
                    $this->parameters[substr($match, 1, strlen($match) - 2)],
                    $subject,
                );
            }
        }

        return $subject;
    }
}
