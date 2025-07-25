<?php

namespace App\Services;

use App\Models\Spa;

class SpasService
{
    public function store(array $array): Spa
    {
        $spa = Spa::create($array);

        return $spa;
    }

    public function update(Spa $spa, array $array): Spa
    {
        $spa->update($array);

        return $spa;
    }
}
