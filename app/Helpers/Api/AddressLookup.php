<?php

namespace App\Helpers\Api;

class AddressLookup
{
    public function __construct(

        public $addressKey = null,
        public $type = null,
        public $uprn = null,
        public $districtCode = null,
        public $coordinatesEasting = null,
        public $coordinatesNorthing = null,
        public $latitude = null,
        public $longitude = null,
        public $label = null,
        public $organisationName = null,
        public $subPremises = null,
        public $premisesName = null,
        public $dependentThoroughfareName = null,
        public $thoroughfareNumber = null,
        public $thoroughfareName = null,
        public $doubleDependentLocality = null,
        public $locality = null,
        public $postTown = null,
        public $postCode,
        public $county = null,
        public $country = null
    ) {}

    public function getFullAddress(): string
    {
        return implode(', ', array_filter([
            $this->organisationName,
            $this->subPremises,
            $this->premisesName,
            $this->thoroughfareNumber,
            $this->thoroughfareName,
            $this->dependentThoroughfareName,
            $this->postTown,
            $this->postCode,
            $this->country,
        ]));
    }
}
