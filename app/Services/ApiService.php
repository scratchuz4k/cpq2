<?php

namespace App\Services;

use App\Models\Api;
use Illuminate\Support\Facades\DB;

class ApiService
{
    public function store(array $array): Api
    {
        $api = Api::create($array);

        return $api;
    }

    public function update(Api $api, array $array): Api
    {
        $api->update($array);

        return $api;
    }
}
