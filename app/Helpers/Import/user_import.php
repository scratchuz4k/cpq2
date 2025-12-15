<?php

return [
    'data' => [
        [
            'name' => 'Company Admin',
            'email' => 'companyadmin@test.com',
            'password' => 'password',
            'sso' => 0,
        ],
        [
            'name' => 'Support',
            'email' => 'support@test.com',
            'password' => 'password',
            'sso' => 1,
        ],
        [
            'name' => 'Sales Manager',
            'email' => 'salesmanager@test.com',
            'password' => 'password',
            'sso' => 0,
        ],
        [
            'name' => 'Sales Rep',
            'email' => 'salesrep@test.com',
            'password' => 'password',
            'sso' => 0,
        ],
    ],
    'filename' => 'import_users_template.csv',
    'columns' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'sso' => 'SSO',
    ],
];
