<?php

namespace App\Domains\UserTracking\Actions;

use App\Domains\TrailerTrader\TrailerTraderDomain;

class GetPageNameFromUrlAction
{
    public const PAGE_NAMES = [
        'TT_PLP' => 'TT_PLP_PAGE',
        'TT_PDP' => 'TT_PDP_PAGE',
        'TT_DEALER' => 'TT_DEALER_PAGE',

        'AF_PLP' => 'AF_PLP_PAGE',
        'AF_PDP' => 'AF_PDP_PAGE',

        'DW_PLP' => 'DW_PLP_PAGE',
        'DW_PDP' => 'DW_PDP_PAGE',
    ];

    public const SITE_TT_AF = 'TT_AF';

    public const SITE_DW = 'DW';

    public const PAGE_NAME_TO_SITE = [
        'TT_PLP_PAGE' => self::SITE_TT_AF,
        'TT_PDP_PAGE' => self::SITE_TT_AF,
        'TT_DEALER_PAGE' => self::SITE_TT_AF,

        'AF_PLP_PAGE' => self::SITE_TT_AF,
        'AF_PDP_PAGE' => self::SITE_TT_AF,

        'DW_PLP_PAGE' => self::SITE_DW,
        'DW_PDP_PAGE' => self::SITE_DW,
    ];

    public const PAGE_NAME_TO_TOTAL_COUNT_COLUMN = [
        'TT_PLP_PAGE' => 'plp_total_count',
        'TT_PDP_PAGE' => 'pdp_total_count',
        'TT_DEALER_PAGE' => 'tt_dealer_page_total_count',

        'AF_PLP_PAGE' => 'plp_total_count',
        'AF_PDP_PAGE' => 'pdp_total_count',

        'DW_PLP_PAGE' => 'plp_total_count',
        'DW_PDP_PAGE' => 'pdp_total_count',
    ];

    public function __construct(private TrailerTraderDomain $trailerTraderDomain)
    {
    }

    public function execute(string $url): ?string
    {
        foreach ($this->templates() as $template) {
            if (array_key_exists('check', $template) && is_callable($template['check'])) {
                $shouldCheck = call_user_func($template['check'], $url);

                if (!$shouldCheck) {
                    continue;
                }
            }

            if (empty($template['regex'])) {
                continue;
            }

            $hasMatches = preg_match($template['regex'], $url);

            if ($hasMatches === 1) {
                return $template['page_name'];
            }
        }

        return null;
    }

    /**
     * IMPORTANT: We need to update the regex for each template when there is an update
     * on the frontend side. If we don't do this then the page_name will result in NULL!
     *
     * @return array[]
     */
    private function templates(): array
    {
        return [[
            'check' => $this->trailerTraderFrontendDomainCheck(),
            // Example: https://trailertrader.com/trailers-for-sale/watercraft-trailers-for-sale?sort=-createdAt
            'regex' => '/(http|https):\/\/.*\/(trailers-for-sale).*/',
            'page_name' => self::PAGE_NAMES['TT_PLP'],
        ], [
            'check' => $this->trailerTraderFrontendDomainCheck(),
            // Example: https://trailertrader.com/new-2023-load-rite-146-v-bunk-boat-trailer--QS9o.html
            'regex' => '/(http|https):\/\/.*\/(.*).html/',
            'page_name' => self::PAGE_NAMES['TT_PDP'],
        ], [
            'check' => $this->trailerTraderFrontendDomainCheck(),
            // Example: https://trailertrader.com/trailer-dealer-in-West-Berlin-NJ/Franklin-Trailers,-Inc.-trailer-sales
            'regex' => '/(http|https):\/\/.*\/(trailer-dealer-.*)\/.*/',
            'page_name' => self::PAGE_NAMES['TT_DEALER'],
        ]];
    }

    private function trailerTraderFrontendDomainCheck(): callable
    {
        return fn (string $url): bool => $this->trailerTraderDomain->isFrontendDomain($url);
    }
}
