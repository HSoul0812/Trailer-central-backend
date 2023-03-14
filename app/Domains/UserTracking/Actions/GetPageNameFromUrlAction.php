<?php

namespace App\Domains\UserTracking\Actions;

use App\Domains\TrailerTrader\TrailerTraderDomain;

class GetPageNameFromUrlAction
{
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
            'regex' => '/[http|https]:\/\/.*\/(trailers-for-sale).*/',
            'page_name' => 'TT_PLP',
        ], [
            'check' => $this->trailerTraderFrontendDomainCheck(),
            // Example: https://trailertrader.com/new-2023-load-rite-146-v-bunk-boat-trailer--QS9o.html
            'regex' => '/[http|https]:\/\/.*\/(.*).html/',
            'page_name' => 'TT_PDP',
        ]];
    }

    private function trailerTraderFrontendDomainCheck(): callable
    {
        return fn(string $url): bool => $this->trailerTraderDomain->isFrontendDomain($url);
    }
}
