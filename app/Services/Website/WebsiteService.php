<?php

namespace App\Services\Website;

use App\Repositories\Website\DealerProxyRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class WebsiteService
 * @package App\Services\Website
 */
class WebsiteService
{
    /**
     * @var DealerProxyRepositoryInterface
     */
    private $dealerProxyRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * WebsiteService constructor.
     * @param DealerProxyRepositoryInterface $dealerProxyRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(DealerProxyRepositoryInterface $dealerProxyRepository, WebsiteRepositoryInterface $websiteRepository)
    {
        $this->dealerProxyRepository = $dealerProxyRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function enableProxiedDomainSsl(int $websiteId): bool
    {
        try {
            $website = $this->websiteRepository->get(['id' => $websiteId]);
            $www = 'www.';

            if (strpos($website->domain, $www) === 0) {
                $secondDomain = substr_replace($website->domain, '', 0, strlen($www));
            } else {
                $secondDomain = $www . $website->domain;
            }

            $result1 = $this->dealerProxyRepository->create(['domain' => $website->domain, 'value' => true]);
            $result2 = $this->dealerProxyRepository->create(['domain' => $secondDomain, 'value' => true]);

            $result = $result1 && $result2;
        } catch (\Exception $e) {
            Log::error('Enable proxied domain error. Website ID - ' . $websiteId, $e->getTrace());
            return false;
        }

        if (!$result) {
            Log::error('Can\'t enable proxied domain for SSL. Website ID - ' . $websiteId);
        } else {
            Log::info('Proxied domain for SSL has been successfully enabled. Website ID - ' . $websiteId);
        }

        return $result;
    }
}
