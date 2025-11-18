<?php

namespace App\Helpers\Api\Cloudaudit;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class CloudAuditApiEmail extends CloudAuditApi
{
    public function __construct() {}

    public function getEmails()
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)->get(env('CLOUDAUDIT_API_URL').'authorisationEmail');

        return $res->json();
    }

    public function getEmail($id)
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)->get(env('CLOUDAUDIT_API_URL').'authorisationEmail/'.$id);

        return $res->json();
    }

    public function getEmailByAccount($account)
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)->get(env('CLOUDAUDIT_API_URL').'authorisationEmail?limit=1&accountId='.$account);

        return $res->json();
    }

    public function storeEmail($id, $name, $rEmail, $rName, $message)
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)
            ->withHeaders(['idempotencyKey' => Hash::make(now())])
            ->post(env('CLOUDAUDIT_API_URL').'authorisationEmail', [
                'accountId' => $id,
                'senderName' => $name,
                'recipientEmail' => $rEmail,
                'recipientName' => $rName,
                'customMessage' => $message,
            ]);

        $data = $res->json();
        if (! $res->successful()) {
            return ['errors' => $data['detail']];
        }

        return $data;
    }
}
