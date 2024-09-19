<?php

namespace App\Helpers;


class CloudMarketAddress
{
    public function __construct(
        public $postCode,
        public $uprn,
        public $lat = null,
        public $lon = null,
        public $addressKey = null,
        public $buildingName = null,
        public $buildingNumber = null,
        public $subPremises = null,
        public $street = null,
        public $town = null,
    ) {}
}
