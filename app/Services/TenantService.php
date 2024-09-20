<?php

namespace App\Services;

use App\Models\Tenant;
use App\Repositories\TenantRepository;
use Illuminate\Support\Facades\DB;

class TenantService
{
    public function __construct(protected TenantRepository $tenantRepository){}
    public function update(Tenant $tenant, array $data)
    {
        return DB::transaction(function () use ($tenant, $data) {
            if (
                array_key_exists('file', $data) && !is_null($data['file'])
                || array_key_exists('sso_text_area', $data) && !is_null($data['sso_text_area'])
            ) {
                $data = [
                    'config_id' => $this->tenantRepository->createSooFileForTenant($data, $tenant)
                ];
            }

            $t = $tenant->update($data);

            return $tenant;
        });
    }
}
