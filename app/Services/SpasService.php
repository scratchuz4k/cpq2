<?php

namespace App\Services;

use App\Models\Spa;
use Illuminate\Support\Facades\DB;

class SpasService
{
    public function store(array $array): Spa
    {
        return DB::transaction(function () use ($array) {
            $spa = Spa::create($array);

            return $spa;
        });
    }

    public function update(Spa $spa, array $array): Spa
    {
        return DB::transaction(function () use ($spa, $array) {
            $spa->update($array);

            return $spa;
        });
    }
}
