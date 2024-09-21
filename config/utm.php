<?php declare(strict_types=1);

return [
    'include' => env('UTM_INCLUDE', false),

    'params' => [
        'utm_source' => env('UTM_SOURCE', 'google'),
        'utm_medium' => env('UTM_MEDIUM', 'referral'),
        'utm_campaign' => env('UTM_CAMPAIGN', 'euvsd_info'),
        'utm_content' => 'auto',
    ]
];
