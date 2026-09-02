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

    'contabilidad' => [
        'url' => env('CONTABILIDAD_API_URL', 'http://contabilidad-api.test/api/v1'),
        'web_url' => env('CONTABILIDAD_WEB_URL', 'http://contabilidad-api.test/contabilidad'),
        'token' => env('CONTABILIDAD_API_TOKEN'),
        'cliente_uuid' => env('CONTABILIDAD_CLIENTE_UUID'),
        'connect_timeout' => env('CONTABILIDAD_CONNECT_TIMEOUT', 3),
        'timeout' => env('CONTABILIDAD_TIMEOUT', 10),
    ],

    'sdi_sso' => [
        'key' => env('SDI_SSO_KEY', 'sdi-local-integracion-2026-cambiar-en-produccion'),
        'alimentos_url' => env('ALIMENTOS_SSO_URL', 'http://alimentos-laravel13.test:8080/sso/vet-sdi'),
        'farmacia_url' => env('VETERFARMA_URL', 'http://127.0.0.1:8080'),
    ],

    'sdi_hub' => [
        'enabled' => env('SDI_HUB_ENABLED', true),
        'url' => env('SDI_HUB_URL', 'http://servidor-local.test'),
        'app' => env('SDI_HUB_APP', 'vet-sdi-v13'),
        'key' => env('SDI_HUB_KEY', 'sdi-local-vet-2026'),
        'timeout' => env('SDI_HUB_TIMEOUT', 5),
    ],

    'personas' => [
        'url' => env('PERSONAS_API_URL', 'http://localhost:8080/personas-api/public'),
        'token' => env('PERSONAS_API_TOKEN'),
        'connect_timeout' => env('PERSONAS_API_CONNECT_TIMEOUT', 2),
        'timeout' => env('PERSONAS_API_TIMEOUT', 5),
    ],
];
