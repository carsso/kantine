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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'webex' => [
        'bearer_token' => env('WEBEX_BOT_TOKEN'),
        'bot_name' => env('WEBEX_BOT_NAME', 'kantine.menu@webex.bot'),
    ],

    'slack' => [
        'webhook_url_failed' => env('SLACK_WEBHOOK_URL_FAILED'),
        'webhook_url_success' => env('SLACK_WEBHOOK_URL_SUCCESS'),
        'notifications_enabled' => env('SLACK_NOTIFICATIONS_ENABLED', false),
    ],

];
