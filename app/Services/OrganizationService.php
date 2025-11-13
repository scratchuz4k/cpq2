<?php

namespace App\Services;

use App\Models\Cloudaudit\Organization;
use Illuminate\Support\Str;

class OrganizationService
{
    public function store(array $array): Organization
    {
        $array['code'] = Str::slug($array['name']);
        $array['status'] = 'created';
        $organization = Organization::create($array);

        return $organization;
    }

    public function update(Organization $organization, array $array): Organization
    {
        $array['code'] = Str::slug($array['name']);
        $organization->update($array);

        return $organization;
    }
}
