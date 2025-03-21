<?php

namespace App\Services;

use App\Models\Merchant;
use Illuminate\Support\Facades\DB;

class MerchantService
{
    public function store(array $array): Merchant
    {
        $merchant = Merchant::create($array);

        return $merchant;
    }

    public function update(Merchant $merchant, array $array): Merchant
    {
        $merchant->update($array);

        return $merchant;
    }
}
