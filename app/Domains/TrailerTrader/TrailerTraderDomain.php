<?php

namespace App\Domains\TrailerTrader;

class TrailerTraderDomain
{
    /**
     * Check if the given url is the trailer trader frontend domain
     * @param string $url
     * @return bool
     */
    public function isFrontendDomain(string $url): bool
    {
        return $this->isInDomainArray($url, config('trailertrader.domains.frontend'));
    }

    /**
     * Check if the given url is the trailer trader backend url
     *
     * @param string $url
     * @return bool
     */
    public function isBackendDomain(string $url): bool
    {
        return $this->isInDomainArray($url, config('trailertrader.domains.backend'));
    }

    /**
     * Check if the given url is trailer trader domain
     *
     * @param string $url
     * @return bool
     */
    public function isTrailerTraderDomain(string $url): bool
    {
        return $this->isFrontendDomain($url) || $this->isBackendDomain($url);
    }

    /**
     * We need to be proactive about this, in case the user send $domain
     * with only the hostname, or with everything (path, query string, etc).
     *
     * We only want to return the host from this method
     *
     * @param string $domain
     * @return string|null
     */
    public function getHostFromDomainString(string $domain): ?string
    {
        $parsed = parse_url($domain);

        // If user send only hostname like `trailertrader.com` then it will
        // fall into the 'path' key, while the host key won't be in the parsed
        // array at all
        $host = data_get($parsed, 'host');

        if ($host === null) {
            $host = data_get($parsed, 'path');
        }

        if (empty($host)) {
            return null;
        }

        return $host;
    }

    /**
     * Check if the given url is in the domain array
     *
     * @param string $url
     * @param array $domains
     * @return bool
     */
    private function isInDomainArray(string $url, array $domains): bool
    {
       $host = $this->getHostFromDomainString($url);

        if ($host === null) {
            return false;
        }

        return collect($domains)->contains($host);
    }
}
