<?php

return [
    'data' => [
        [
            'product_sku' => 'CP1001',
            'bundle_item_sku' => 'CP1002',
            'bundle_item_quantity' => '2',
        ],
        [
            'product_sku' => 'CP1001',
            'bundle_item_sku' => 'CP1003',
            'bundle_item_quantity' => '1',
        ],
    ],
    'filename' => 'import_product_bundles_template.csv',
    'columns' => [
        'product_sku' => 'Product SKU',
        'bundle_item_sku' => 'Bundle Item SKU',
        'bundle_item_quantity' => 'Bundle Item Quantity',
    ],
];
