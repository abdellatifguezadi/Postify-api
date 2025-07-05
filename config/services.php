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

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID', '716713197417108'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', 'https://c2ba-41-141-226-108.ngrok-free.app/api/facebook/callback'),
        'default_graph_version' => 'v18.0',
    ],

    'instagram' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID', env('FACEBOOK_CLIENT_ID', '716713197417108')),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET', env('FACEBOOK_CLIENT_SECRET')),
        'redirect' => env('INSTAGRAM_REDIRECT_URI', 'https://c2ba-41-141-226-108.ngrok-free.app/api/instagram/callback'),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID', '78q232mmzpvttk'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET', 'WPL_AP1.RBxE91EHX2nwPD2b.Yx/YkQ=='),
        'redirect' => env('LINKEDIN_REDIRECT_URI', 'https://oauth.pstmn.io/v1/callback'),
    ],

];
