<?php

return [
    'platzi' => [
        'base_url' => env('PLATZI_API_BASE', 'https://api.escuelajs.co/api/v1'),
        'default_limit' => (int) env('PLATZI_IMPORT_LIMIT', 50),
        'per_page' => (int) env('PLATZI_IMPORT_PER_PAGE', 25),
        'defaults' => [
            'department' => env('PLATZI_DEFAULT_DEPARTMENT', 'Essentials'),
            'brand' => env('PLATZI_DEFAULT_BRAND', 'Platzi'),
            'color' => env('PLATZI_DEFAULT_COLOR', 'multicolor'),
            'inventory' => [
                [
                    'size' => 'OS',
                    'quantity' => 10,
                ],
            ],
        ],
    ],
];
