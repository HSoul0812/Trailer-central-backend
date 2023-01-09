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
    public function certificateDomainSsl(int $websiteId): bool
    {
        $www = 'www.';
        $website = $this->websiteRepository->get(['id' => $websiteId]);
        $domain = strpos($website, $www) !== 0 ? $www . $website->domain : $website->domain;

        $data = [
            "CertificateName" => $website->template,
            "DomainName" => $domain
        ];

        $ch = curl_init("https://i2o2ut7e95.execute-api.us-east-1.amazonaws.com/dev/cloudfront-setup/start");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) {
            Log::error('An error occurred issuing certificate for Website ID - ' . $websiteId . "\n Error: " . $result);
        }

        Log::info('Certificate issued successfully for Website ID - ' . $websiteId);
        return $result;
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
