<?php

return [
    'params' => [
        'merchant-id' => 'dd71f226-0199-11e7-9283-000c295eb8fc',

        // Leave it empty if you're passing the callback url when doing the request
        'callback-url' => '',

        // A summary of your product or application, if needed
        'description' => 'limitless',
    ],

    // Set to true if you are in the development environment
    'testing' => env('ZARINPAL_TESTING', 'false')
];
