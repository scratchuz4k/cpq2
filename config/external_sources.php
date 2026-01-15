<?php

return [
    'cloudaudit' => [
        'live_data' => env('CLOUDAUDIT_LIVE_DATA'),
        'api_auth' => env('CLOUDAUDIT_API_AUTH'),
        'api_url' => env('CLOUDAUDIT_API_URL'),
        'api_id' => env('CLOUDAUDIT_API_ID'),
        'api_client' => env('CLOUDAUDIT_API_CLIENT'),
    ],

    'cloudmarket' => [
        'live_data' => env('CLOUDMARKET_CONNECTION_LIVE_DATA'),
    ],

    'alpnet' => [
        'api_token' => env('ALPNET_API_TOKEN'),
    ],

    'tomtom' => [
        'api_key' => env('TOMTOM_API_KEY'),
    ],

    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'tag_manager_key' => env('GOOGLE_TAG_MANAGER_KEY'),
    ],

    'pandadoc' => [
        'api_key' => env('PANDADOC_API_KEY'),
    ],

    'its' => [
        'connection_token' => env('ITS_CONNECTION_TOKEN'),
    ],

    'virtual1' => [
        'connection_username' => env('VIRTUAL1_CONNECTION_USERNAME'),
        'connection_password' => env('VIRTUAL1_CONNECTION_PASSWORD'),
        'address_username' => env('VIRTUAL1_ADDRESS_USERNAME'),
        'address_password' => env('VIRTUAL1_ADDRESS_PASSWORD'),
    ],

    'gamma' => [
        'connection_username' => env('GAMMA_CONNECTION_USERNAME'),
        'connection_password' => env('GAMMA_CONNECTION_PASSWORD'),
        'connection_client_id' => env('GAMMA_CONNECTION_CLIENTID'),
    ],

    'giacom' => [
        'connection_username' => env('GIACOM_CONNECTION_USERNAME'),
        'connection_password' => env('GIACOM_CONNECTION_PASSWORD'),
    ],

    'ipriver' => [
        'connection_username' => env('IPRIVER_CONNECTION_USERNAME'),
        'connection_password' => env('IPRIVER_CONNECTION_PASSWORD'),
    ],

    'shopblocks' => [
        'api_key' => env('SHOPBLOCKS_API_KEY'),
    ],

    'discord' => [
        'cloudaudit' => [
            'jobs' => env('DISCORD_CLOUDAUDIT_JOBS'),
        ],
        'cloudmarket' => [
            'jobs' => env('DISCORD_CLOUDMARKET_JOBS'),
        ],
    ],
];
