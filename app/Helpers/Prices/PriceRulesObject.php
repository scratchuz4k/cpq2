<?php

namespace App\Helpers\Prices;

use App\Models\Spa;
use App\Models\User;
use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;

class PriceRulesObject
{
    public ?Spa $spa;
    public ?Product $product;
    public ?User $user;
    public ?Supplier $supplier;
    public ?Account $account;

    public function __construct(
        $product = null,
        $user = null,
        $supplier = null,
        $account = null
    ) {
        $this->product = $product;
        $this->user = $user;
        $this->supplier = $supplier;
        $this->account = $account;
    }
}
