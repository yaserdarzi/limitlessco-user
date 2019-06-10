<?php

return [
    'params' => [
        'merchant-id' => 'c77e091a-8b3e-11e9-b6f4-000c29344814',

        // Leave it empty if you're passing the callback url when doing the request
        'callback-url' => '',

        // A summary of your product or application, if needed
        'description' => 'limitless',
    ],

    // Set to true if you are in the development environment
    'testing' => env('ZARINPAL_TESTING', false)
];
