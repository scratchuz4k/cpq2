<?php

namespace App\Services;

use App\Models\Spa;
use Illuminate\Support\Facades\DB;

class SpasService
{
    public function store(array $array): Spa
    {
//        if (isset($array['operation']) && isset($array['data'])) {
//            $array['data'] = json_encode([$array['operation'] => $array['data']]);
//        }

        return DB::transaction(function () use ($array) {
            $spa = Spa::create([
                ...$array,
                "tenant_id" => app("currentTenant")->id,
            ]);

            return $spa;
        });
    }

    public function update(Spa $spa, array $array): Spa
    {
        return DB::transaction(function () use ($spa, $array) {
            $spa->update(
                [
                    ...$array,
                    'tenant_id' => app('currentTenant')->id,
                ]
            );

            return $spa;
        });
    }
}
