<?php

return [
    //rabbitMq config
    'server' => env('RABBIT_SERVER', 'localhost'),
    'user' => env('RABBIT_USER', 'guest'),
    'password' => env('RABBIT_PASSWORD', 'guest'),
    'port' =>env('RABBIT_PORT', '5672'),
];