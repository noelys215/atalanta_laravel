<?php

return [

    'paths' => ['api/*', 'stripe/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*', 'http://localhost:5173', 'https://atalantaimages.s3.amazonaws.com'],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Clear-Cart'],

    'max_age' => 0,

    'supports_credentials' => false,

];
