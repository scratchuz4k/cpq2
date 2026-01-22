<?php

namespace App\Services;

use App\Enums\CloudAuditOperationEnum;
use App\Enums\CloudAuditStatusEnum;
use App\Helpers\Api\Cloudaudit\CloudAuditApiAccount;
use App\Helpers\Api\Cloudaudit\CloudAuditApiEmail;
use App\Models\Cloudaudit\Organisation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrganisationService
{
    public function store(array $array): Organisation
    {
        $array['code'] = Str::slug(Auth::user()->tenant_id.'-'.$array['name']);
        $array['status'] = 'created';
        $organisation = Organisation::create($array);

        if (config('services.cloudaudit.live_data')) {
            $accApi = new CloudAuditApiAccount;
            $data = $accApi->storeAccount($organisation->code, $organisation->name);
            $organisation->update(['status' => CloudAuditStatusEnum::CREATED_ACCOUNT, 'data' => $data]);
            $organisation->logs()->create(['code' => CloudAuditOperationEnum::ACCOUNT, 'data' => $data, 'synced_at' => now()]);

            if (array_key_exists('errors', $data)) {
                return $organisation;
            }

            $emailApi = new CloudAuditApiEmail;
            $data = $emailApi->storeEmail($data['id'], Auth::user()->name, $organisation->email, $organisation->name, '');
            $organisation->update(['status' => CloudAuditStatusEnum::SENT_EMAIL]);
            $organisation->logs()->create(['code' => CloudAuditOperationEnum::EMAIL, 'data' => $data, 'synced_at' => now()]);
        }

        return $organisation;
    }

    public function update(Organisation $organisation, array $array): Organisation
    {
        $array['code'] = Str::slug($array['name']);
        $organisation->update($array);

        return $organisation;
    }
}
