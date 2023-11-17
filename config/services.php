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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'hubspot' => [
        '4c10b4fa-7fac-4c43-88e7-b074180b43dc',
        '6925f713-ef2f-4d81-a0fa-7b605ac605d1',
        'eda7bd55-145f-4a27-92c3-e3b992397674',
        '24da75d3-e794-47b7-bb45-06c81d8a9597',
        'e56687c0-1141-49c7-a706-2fa2941322c8',
        '0af9faf3-c056-4023-8919-e654a384e21f',
        '77c0f622-3bb6-4412-b0b2-c46d424f7171',
        'b77eff0e-3661-4d48-b2d9-0757b35adb1d',
        '9944e7b6-1db4-422b-a839-ed9d020126fc',
        '7f14eef7-c46b-4051-ad1d-557ce3d280e4',
        '09658544-d246-47f3-bea2-3d9f0bcb266d',
        '7938618a-f89c-49db-83f4-18823db33259',
        '5a21b267-6531-44c3-a476-7cd4aae9260f',
        'b170c128-18fd-4157-a714-77977f377b70',
        '2821cfc4-d327-4ea4-b3b0-accedbbbc551',
        '9be94507-6b84-441f-9648-9238fe26ff93',
    ]
];
