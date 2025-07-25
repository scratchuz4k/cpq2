<?php

namespace App\Services;

use App\Models\Region;

class RegionsService
{
    public function store(array $array): Region
    {
        $region = Region::create($array);

        return $region;
    }

    public function update(Region $region, array $array): Region
    {
        $region->update($array);

        return $region;
    }
}
