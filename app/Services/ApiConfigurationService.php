<?php

namespace App\Services;

use App\Models\ApiConfiguration;
use Illuminate\Support\Facades\DB;

class ApiConfigurationService
{
    public function store(array $array): ApiConfiguration
    {
        $api = ApiConfiguration::create($array);

        return $api;
    }

    public function update(ApiConfiguration $api, array $array): ApiConfiguration
    {
        $api->update($array);

        return $api;
    }
}
