<?php

return [
    'rate_limiting' => [
        'user_agent' => [
            'black_list' => env('RATE_LIMITING_USER_AGENT_LIST', ['petalbot', 'bytespider'])
        ]
    ]
];
