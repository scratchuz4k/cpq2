<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SuppliersService
{
    public function store(array $array): Supplier
    {
        return DB::transaction(function () use ($array) {
            $supplier = Supplier::create([
                ...$array,
                "tenant_id" => app("currentTenant")->id,
            ]);


            return $supplier;
        });
    }

    public function update(Supplier $supplier, array $array): Supplier
    {
        return DB::transaction(function () use ($supplier, $array) {
            $supplier->update(
                [
                    ...$array,
                    'tenant_id' => app('currentTenant')->id,
                ]
            );

            return $supplier;
        });
    }
}
