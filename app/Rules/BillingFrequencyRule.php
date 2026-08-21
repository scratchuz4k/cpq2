<?php

namespace App\Rules;

use App\Enums\FrequencyUnitsEnum;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class BillingFrequencyRule implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $contractValue = $this->data['contract_term_value'] ?? null;
        $contractUnit = $this->data['contract_term_unit'] ?? null;
        $billingUnit = $this->data['billing_frequency_unit'] ?? null;

        if (! $contractValue || ! $contractUnit || ! $value || ! $billingUnit) {
            return;
        }

        $daysPerUnit = FrequencyUnitsEnum::daysPerUnit();

        $contractLengthDays = $contractValue * ($daysPerUnit[$contractUnit] ?? 0);
        $billingLengthDays = $value * ($daysPerUnit[$billingUnit] ?? 0);

        if ($billingLengthDays <= 0) {
            return;
        }

        if ($contractLengthDays % $billingLengthDays !== 0) {
            $fail('The billing frequency must evenly divide the contract term.');
        }
    }
}
