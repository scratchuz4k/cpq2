<?php

// Columns for the quote summary simple table
return [
    ['name' => 'Provider',  'value' => 'provider', 'is_group' => true],
    ['name' => 'SubProvider',  'value' => 'subprovider', 'is_group' => true],
    ['name' => 'Name', 'value' => 'name', 'is_group' => true],
    ['name' => 'Quote Reference',  'value' => 'quote_ref', 'is_group' => false],
    ['name' => 'Speed',  'value' => 'speed', 'is_group' => false],
    ['name' => 'Bearer',  'value' => 'bearer', 'is_group' => false],
    ['name' => 'Term', 'value' => 'renewal_terms', 'is_group' => false],
    ['name' => 'Frequency', 'value' => 'billing_frequency', 'is_group' => false],
    ['name' => 'Price Plan',  'value' => 'price_plan.name', 'is_group' => false],
    ['name' => 'Quantity', 'value' => 'quantity', 'is_group' => false],
    ['name' => 'Price', 'value' => 'price', 'is_group' => false],
    ['name' => 'Total',  'value' => 'total', 'is_group' => false],
];
