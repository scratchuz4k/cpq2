<?php

namespace App\Services;

use App\Enums\CloudAuditOperationEnum;
use App\Enums\CloudAuditStatusEnum;
use App\Helpers\Api\Cloudaudit\CloudAuditApiAccount;
use App\Helpers\Api\Cloudaudit\CloudAuditApiEmail;
use App\Models\Cloudaudit\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrganizationService
{
    public function store(array $array): Organization
    {
        $array['code'] = Str::slug(Auth::user()->tenant_id.'-'.$array['name']);
        $array['status'] = 'created';
        $organization = Organization::create($array);

        if (env('CLOUDAUDIT_LIVE_DATA')) {
            $accApi = new CloudAuditApiAccount;
            $data = $accApi->storeAccount($organization->code, $organization->name);
            $organization->update(['status' => CloudAuditStatusEnum::CREATED_ACCOUNT, 'data' => $data]);
            $organization->logs()->create(['code' => CloudAuditOperationEnum::ACCOUNT, 'data' => $data, 'synced_at' => now()]);

            if (array_key_exists('errors', $data)) {
                return $organization;
            }

            $emailApi = new CloudAuditApiEmail;
            $data = $emailApi->storeEmail($data['id'], $organization->name, $organization->email, $organization->name, '');
            $organization->update(['status' => CloudAuditStatusEnum::SENT_EMAIL]);
            $organization->logs()->create(['code' => CloudAuditOperationEnum::EMAIL, 'data' => $data, 'synced_at' => now()]);
        }

        return $organization;
    }

    public function update(Organization $organization, array $array): Organization
    {
        $array['code'] = Str::slug($array['name']);
        $organization->update($array);

        return $organization;
    }
}
