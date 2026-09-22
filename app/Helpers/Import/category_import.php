<?php

return [
    'data' => [
        [
            'code' => 'test-cat-1',
            'name' => 'Test Category 1',
            'is_active' => '1',
            'parent_code' => '',
        ],
        [
            'code' => 'test-cat-2',
            'name' => 'Test Category 2',
            'is_active' => '0',
            'parent_code' => 'test-cat-1',
        ],
        [
            'code' => 'test-cat-3',
            'name' => 'Test Category 3',
            'is_active' => '1',
            'parent_code' => 'test-cat-1',
        ],
        [
            'code' => 'test-cat-4',
            'name' => 'Test Category 4',
            'is_active' => '1',
            'parent_code' => 'test-cat-1',
        ],
        [
            'code' => 'test-cat-6',
            'name' => 'Test Category 6',
            'is_active' => '1',
            'parent_code' => 'test-cat-5',
        ],
        [
            'code' => 'test-cat-5',
            'name' => 'Test Category 5',
            'is_active' => '1',
            'parent_code' => '',
        ],
    ],
    'filename' => 'import_categories_template.csv',
    'columns' => [
        'code' => 'Code',
        'name' => 'Name',
        'is_active' => 'Is Active',
        'parent_code' => 'Parent Code',
    ],
];
