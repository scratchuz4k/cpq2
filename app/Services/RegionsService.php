<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Support\Facades\DB;

class RegionsService
{
    public function store(array $array): Region
    {
        return DB::transaction(function () use ($array) {
            $region = Region::create($array);
            return $region;
        });
    }

    public function update(Region $region, array $array): Region
    {
        return DB::transaction(function () use ($region, $array) {
            $region->update($array);
            return $region;
        });
    }
}
