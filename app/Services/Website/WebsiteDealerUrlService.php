<?php

namespace App\Services\Website;

use App\Helpers\SanitizeHelper;
use App\Models\Region;
use App\Models\Website\WebsiteDealerUrl;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\Website\WebsiteDealerUrlRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class WebsiteDealerUrlService
 * @package App\Services\Website
 */
class WebsiteDealerUrlService implements WebsiteDealerUrlServiceInterface
{
    /**
     * @var DealerLocationRepositoryInterface
     */
    private $dealerLocationRepository;

    /**
     * @var WebsiteDealerUrlRepositoryInterface
     */
    private $websiteDealerUrlRepository;

    /**
     * @var SanitizeHelper
     */
    private $sanitizeHelper;

    /**
     * WebsiteDealerUrlService constructor.
     * @param DealerLocationRepositoryInterface $dealerLocationRepository
     * @param WebsiteDealerUrlRepositoryInterface $websiteDealerUrlRepository
     * @param SanitizeHelper $sanitizeHelper
     */
    public function __construct(
        DealerLocationRepositoryInterface $dealerLocationRepository,
        WebsiteDealerUrlRepositoryInterface $websiteDealerUrlRepository,
        SanitizeHelper $sanitizeHelper
    ) {
        $this->dealerLocationRepository = $dealerLocationRepository;
        $this->websiteDealerUrlRepository = $websiteDealerUrlRepository;
        $this->sanitizeHelper = $sanitizeHelper;
    }

    /**
     * @return array
     */
    public function generate(): array
    {
        $dealerLocationIds = $this->dealerLocationRepository->getAll(['per_page' => 100000])->pluck('dealer_location_id');

        $updatedDealerLocationUrls = 0;
        $couldNotUpdatedDealerLocationUrls = [];

        foreach ($dealerLocationIds as $dealerLocationId) {
            try {
                $result = $this->generateByLocationId($dealerLocationId);

                if ($result) {
                    $updatedDealerLocationUrls++;
                } else {
                    $couldNotUpdatedDealerLocationUrls[] = $dealerLocationId;
                }
            } catch (\Exception $e) {
                Log::error('Website dealer url generate error.', $e->getTrace());
                $couldNotUpdatedDealerLocationUrls[] = $dealerLocationId;
            }
        }

        return compact(['updatedDealerLocationUrls', 'couldNotUpdatedDealerLocationUrls']);
    }

    /**
     * @param int $locationId
     * @return bool
     */
    public function generateByLocationId(int $locationId): bool
    {
        $location = $this->dealerLocationRepository->get(['dealer_location_id' => $locationId]);

        if (!$location->locationRegion instanceof Region) {
            return false;
        }

        $cityAndState = $location->city . ' ' . $location->locationRegion->region_name;
        $dealerName = $location->user->name;

        [$cityAndState, $dealerName] = $this->sanitizeHelper->sanitizePieces([$cityAndState, $dealerName]);
        $url = '/trailer-dealer-in-' . implode('/', [$cityAndState, $dealerName]);

        $originalUrl = $url;

        $x = 2;
        while($this->websiteDealerUrlRepository->exists(['url' => $url, 'not_location_id' => $locationId])) {
            $url = $originalUrl . '-' . $x;
            $x++;
        }

        $params = [
            'location_id' => $locationId,
            'dealer_id' => $location->dealer_id,
            'url' => $url,
        ];

        if ($this->websiteDealerUrlRepository->exists(['location_id' => $locationId])) {
            $result = $this->websiteDealerUrlRepository->update($params);
        } else {
            $result = $this->websiteDealerUrlRepository->create($params);
        }

        return $result instanceof WebsiteDealerUrl;
    }
}
