<?php

namespace App\Helpers\Api;

class AddressLookup
{
    public function __construct(

        public $addressKey,
        public $type,
        public $uprn,
        public $districtCode,
        public $coordinatesEasting,
        public $coordinatesNorthing,
        public $latitude,
        public $longitude,
        public $label,
        public $organisationName,
        public $subPremises,
        public $premisesName,
        public $dependentThoroughfareName,
        public $thoroughfareNumber,
        public $thoroughfareName,
        public $doubleDependentLocality,
        public $locality,
        public $postTown,
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
