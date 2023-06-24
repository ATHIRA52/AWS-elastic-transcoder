<?php

use Aws\Laravel\AwsServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration options set in this file will be passed directly to the
    | `Aws\Sdk` object, from which all client objects are created. This file
    | is published to the application config directory for modification by the
    | user. The full set of possible options are documented at:
    | http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
    |
    */
    'credentials' => [
        'key'    => env('AWS_ACCESS_KEY_ID', 'AKIAWXFAR2G6MUD3XD4C'),
        'secret' => env('AWS_SECRET_ACCESS_KEY', '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'),
    ],
    'region' => env('AWS_REGION', 'ap-south-1'),
    'version' => 'latest',
    'ua_append' => [
        'L5MOD/' . AwsServiceProvider::VERSION,
    ],
     'Ses' => [
        'region' => 'ap-south-1',
    ],
];
