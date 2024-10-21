<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class BrandService
{
    public function store(array $array): Brand
    {
        return DB::transaction(function () use ($array) {
            $brand = Brand::create($array);


            return $brand;
        });
    }

    public function update(Brand $brand, array $array): Brand
    {
        return DB::transaction(function () use ($brand, $array) {
            $brand->update($array);

            return $brand;
        });
    }
}
