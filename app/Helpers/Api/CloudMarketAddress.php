<?php

namespace App\Helpers\Api;


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

    public function all(): string
    {
        return $this->postCode . $this->uprn . $this->lat . $this->lon;
    }
}
