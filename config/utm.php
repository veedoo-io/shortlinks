<?php declare(strict_types=1);

return [
    'include' => env('UTM_INCLUDE', false),

    'params' => [
        'utm_source' => env('UTM_SOURCE', 'euvsd.info'),
        'utm_medium' => 'auto',
        'utm_campaign' => env('UTM_CAMPAIGN', 'euvsd_info'),
    ]
];
