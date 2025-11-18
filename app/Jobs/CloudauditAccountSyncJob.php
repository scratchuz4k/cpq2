<?php

namespace App\Jobs;

use App\Enums\CloudAuditOperationEnum;
use App\Enums\CloudAuditStatusEnum;
use App\Helpers\Api\Cloudaudit\CloudAuditApiAccount;
use App\Helpers\Api\Cloudaudit\CloudAuditApiEmail;
use App\Helpers\Api\Cloudaudit\CloudAuditApiReport;
use App\Models\Cloudaudit\Organization;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class CloudauditAccountSyncJob implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

    // public function middleware()
    // {
    //      return [(new WithoutOverlapping('sync:CloudauditAccountSyncJob'))->expireAfter(180)];
    // }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $apiAccount = new CloudAuditApiAccount;
        $apiEmail = new CloudAuditApiEmail;
        $apiReport = new CloudAuditApiReport;
        $accs = $apiAccount->getAccounts();

        foreach ($accs as $acc) {
            $org = Organization::where('code', $acc['accountExternalId'])->first();
            if (! $org || in_array($org->status, [CloudAuditStatusEnum::GRANTED, CloudAuditStatusEnum::REVOKED])) {
                continue;
            }

            $email = $apiEmail->getEmailByAccount($acc['id']);
            if (! $email) {
                continue;
            }
            $email = is_array($email) ? $email[0] : $email;
            $orgEmail = $org->email();
            if ($email['status'] != $orgEmail->data['status']) {
                $orgEmail->update(['data' => $email, 'synced_at' => now()]);
                $org->update(['status' => $email['status']]);
            }

            if (strtoupper($acc['adminConsentStatus']) != 'PENDING') {
                $org->update(['status' => strtoupper($acc['adminConsentStatus']) == 'GRANTED' ? CloudAuditStatusEnum::GRANTED : CloudAuditStatusEnum::REVOKED]);
            }

            $orgReport = $org->report();
            if (! $orgReport && strtoupper($acc['adminConsentStatus']) == 'GRANTED') {
                $data = $apiReport->getReportByAccount($acc['id']);
                if (array_key_exists('errors', $data)) {
                    Log::error($data);
                    $org->update(['status' => CloudAuditStatusEnum::PENDING]);

                    continue;
                }
                $org->logs()->create(['code' => CloudAuditOperationEnum::REPORT, 'data' => $data, 'synced_at' => now()]);
            }

        }

    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] CloudauditAccount failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'Cloudaudit',
            'response' => $exception->getMessage(),
        ];

        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] CloudauditAccount failed with error: '.json_encode($error);

        Log::error($message);
        if (env('DISCORD_CLOUDAUDIT_JOBS')) {
            Http::post(env('DISCORD_CLOUDAUDIT_JOBS'), ['content' => $message]);
        }
    }
}
