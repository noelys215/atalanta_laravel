<?php

return [

    'paths' => ['api/*', 'stripe/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*', 'http://localhost:5173', 'https://atalantaimages.s3.amazonaws.com', 'https://xx82fv3rgu.us-east-1.awsapprunner.com'],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Clear-Cart'],

    'max_age' => 0,

    'supports_credentials' => false,

];
