<?php

use App\Models\QuoteItem;

it('divides renewal term by billing frequency to get the number of payments', function () {
    $item = new QuoteItem([
        'renewal_terms_value' => 36,
        'renewal_terms_unit' => 'MONTHS',
        'billing_frequency_value' => 6,
        'billing_frequency_unit' => 'MONTHS',
    ]);

    expect($item->numberOfPayments())->toBe(6.0);
});

it('converts renewal term and billing frequency across different units', function () {
    $item = new QuoteItem([
        'renewal_terms_value' => 24,
        'renewal_terms_unit' => 'MONTHS',
        'billing_frequency_value' => 90,
        'billing_frequency_unit' => 'DAYS',
    ]);

    expect($item->numberOfPayments())->toBe(8.0);
});

it('falls back to a single payment when billing frequency is missing', function () {
    $item = new QuoteItem([
        'renewal_terms_value' => null,
        'renewal_terms_unit' => null,
        'billing_frequency_value' => null,
        'billing_frequency_unit' => null,
    ]);

    expect($item->numberOfPayments())->toBe(1.0);
});
