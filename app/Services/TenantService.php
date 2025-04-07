<?php

namespace App\Services;

use App\Models\Tenant;
use App\Repositories\TenantRepository;

class TenantService
{
    public function __construct(protected TenantRepository $tenantRepository) {}
    public function update(Tenant $tenant, array $data)
    {
        if (array_key_exists('sso_text_area', $data) && !is_null($data['sso_text_area'])) {
            $data = [
                'sso_id' => $this->tenantRepository->createSooFileForTenant($data, $tenant)
            ];
        }

        if (
            array_key_exists('configurations', $data)
            && is_array($data['configurations'])
        ) {
            $tenant->syncConfigurations($data['configurations']);
        }

        $tenant->update($data);

        return $tenant->refresh();
    }
}
