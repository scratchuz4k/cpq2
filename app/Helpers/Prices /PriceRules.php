<?php

namespace App\Helpers\Prices;

use App\Models\Price;
use Illuminate\Pipeline\Pipeline;
use App\Helpers\Prices\SpaFromUserRule;
use App\Helpers\Prices\SpaFromProductRule;
use App\Helpers\Prices\SpaFromAncestorsRule;

class PriceRules
{
    private $priceRules = [
        SpaFromProductRule::class,
        SpaFromUserRule::class,
        SpaFromAncestorsRule::class,
    ];

    private Price $price;

    public function __construct(Price $price)
    {
        $this->price = $price;
    }

    public function apply()
    {
        return app(Pipeline::class)->send($this->price)->through($this->priceRules)->thenReturn();
    }
}
