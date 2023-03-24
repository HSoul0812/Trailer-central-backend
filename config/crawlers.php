<?php

return [
    'providers' => [
        'google' => [
            'provider_name' => 'Google',
            'strategy' => \App\Domains\Crawlers\Strategies\CrawlerCheckStrategy::IP_CHECK,
            'ips_cache_key' => 'google-bot-ips',
            'url' => 'https://developers.google.com/static/search/apis/ipranges/googlebot.json',
        ],
        'bing' => [
            'provider_name' => 'Bing',
            'strategy' => \App\Domains\Crawlers\Strategies\CrawlerCheckStrategy::IP_CHECK,
            'ips_cache_key' => 'bing-bot-ips',
            'url' => 'https://www.bing.com/toolbox/bingbot.json',
        ],
        'yahoo' => [
            'provider_name' => 'Yahoo',
            'strategy' => \App\Domains\Crawlers\Strategies\CrawlerCheckStrategy::USER_AGENT_CHECK,
            'user_agents' => [
                // From https://help.yahoo.com/kb/SLN22600.html
                'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
            ],
        ],
    ],

    'report' => [
        'cache_crawlers_ip_addresses' => [
            'send_mail' => env('CRAWLERS_REPORT_CACHE_CRAWLERS_IP_ADDRESSES_SEND_MAIL', false),
            'mail_to' => [
                'pond@trailercentral.com',
                'francois@trailercentral.com',
            ],
        ],
    ],
];
