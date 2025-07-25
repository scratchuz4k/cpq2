<?php

namespace App\Services;

use App\Models\Supplier;

class SuppliersService
{
    public function store(array $array): Supplier
    {
        $supplier = Supplier::create($array);

        return $supplier;
    }

    public function update(Supplier $supplier, array $array): Supplier
    {
        $supplier->update($array);

        return $supplier;
    }
}
