<?php

return [

    'paths' => ['api/*', 'stripe/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Clear-Cart'], // Expose the custom header

    'max_age' => 0,

    'supports_credentials' => false,

];
