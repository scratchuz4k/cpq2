<?php

return [
    'data' => [
        [
            'name' => 'Mobile',
            'solution_type_id' => '1',
            'sort_order' => '1',
            'is_active' => '1',
        ],
        [
            'name' => 'Hardware',
            'solution_type_id' => '1',
            'sort_order' => '2',
            'is_active' => '1',
        ],
        [
            'name' => 'Cloud',
            'solution_type_id' => '1',
            'sort_order' => '3',
            'is_active' => '0',
        ],
    ],
    'filename' => 'import_steps_template.csv',
    'columns' => [
        'name' => 'Name',
        'solution_type_id' => 'Solution Type ID',
        'sort_order' => 'Sort Order',
        'is_active' => 'Is Active',
    ],
];
