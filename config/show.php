<?php

return [
    'entry_fee_pence' => 50,

    'self_entry_open' => env('SELF_ENTRY_OPEN', false),

    'show_live' => env('BACKUP_MODE', 'pre_show') === 'show',

    'title' => 'Calverley Show 2026',

    'placement_points' => [
        '1st' => 4,
        '2nd' => 2,
        '3rd' => 1,
        'highly_commended' => 0,
    ],

    'result_card_colours' => [
        '1st' => '#dc2626',
        '2nd' => '#2563eb',
        '3rd' => '#16a34a',
        'highly_commended' => '#9ca3af',
    ],
];
