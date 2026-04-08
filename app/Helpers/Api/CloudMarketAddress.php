<?php

namespace App\Helpers\Api;

use Illuminate\Support\Str;

class CloudMarketAddress
{
    public function __construct(
        public $postCode,
        public $uprn = null,
        public $districtCode = null,
        public $lat = null,
        public $lon = null,
        public $addressKey = null,
        public $buildingName = null,
        public $buildingNumber = null,
        public $subPremises = null,
        public $street = null,
        public $town = null,
    ) {}

    public function toString(): string
    {
        return Str::slug($this->postCode).'_'.md5($this->uprn.$this->addressKey.$this->buildingName.$this->buildingNumber.$this->subPremises.$this->street.$this->town);
    }
}
