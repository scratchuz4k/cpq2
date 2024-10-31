<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SuppliersService
{
    public function store(array $array): Supplier
    {
        return DB::transaction(function () use ($array) {
            $supplier = Supplier::create($array);


            return $supplier;
        });
    }

    public function update(Supplier $supplier, array $array): Supplier
    {
        return DB::transaction(function () use ($supplier, $array) {
            $supplier->update($array);

            return $supplier;
        });
    }
}
