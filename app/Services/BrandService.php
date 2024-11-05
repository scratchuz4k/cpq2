<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class BrandService
{
    public function store(array $array): Brand
    {
        $brand = Brand::create($array);

        return $brand;
    }

    public function update(Brand $brand, array $array): Brand
    {
        $brand->update($array);

        return $brand;
    }
}
