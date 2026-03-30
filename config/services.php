<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'saml2' => [
        'metadata' => null,
    ],

    'cloudaudit' => [
        'live_data' => env('CLOUDAUDIT_LIVE_DATA'),
        'api_auth' => env('CLOUDAUDIT_API_AUTH'),
        'api_url' => env('CLOUDAUDIT_API_URL'),
        'api_id' => env('CLOUDAUDIT_API_ID'),
        'api_client' => env('CLOUDAUDIT_API_CLIENT'),
    ],

    'cloudmarket' => [
        'connection_live_data' => env('CLOUDMARKET_CONNECTION_LIVE_DATA'),
        'connection_service_url' => env('CLOUDMARKET_CONNECTION_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com'),
    ],

    'alpnet' => [
        'api_token' => env('ALPNET_API_TOKEN'),
    ],

    'tomtom' => [
        'api_key' => env('TOMTOM_API_KEY'),
    ],

    'google' => [
        'maps' => [
            'api_key' => env('GOOGLE_MAPS_API_KEY'),
        ],
        'tag_manager' => [
            'api_key' => env('GOOGLE_TAG_MANAGER_KEY'),
        ],
        'client_id' => env('GOOGLE_OIDC_CLIENT_ID'),
        'client_secret' => env('GOOGLE_OIDC_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/google',
    ],

    'pandadoc' => [
        'api_key' => env('PANDADOC_API_KEY'),
    ],

    'its' => [
        'connection_token' => env('ITS_CONNECTION_TOKEN'),
        'connection_access_key' => env('ITS_CONNECTION_ACCESSKEY'),
    ],

    'virtual1' => [
        'connection_username' => env('VIRTUAL1_CONNECTION_USERNAME'),
        'connection_password' => env('VIRTUAL1_CONNECTION_PASSWORD'),
        'address_username' => env('VIRTUAL1_ADDRESS_USERNAME', 'JenniferLawrence'),
        'address_password' => env('VIRTUAL1_ADDRESS_PASSWORD', 'secretpass'),
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

    'vmo2' => [
        'connection_app_id' => env('VMO2_CONNECTION_APPID'),
        'connection_client_secret' => env('VMO2_CONNECTION_CLIENTSECRET'),
        'connection_user_id' => env('VMO2_CONNECTION_USERID'),
        'connection_customer_id' => env('VMO2_CONNECTION_CUSTOMERID'),
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
        'shopblocks' => [
            'jobs' => env('DISCORD_SHOPBLOCKS_JOBS'),
        ],
    ],
];
