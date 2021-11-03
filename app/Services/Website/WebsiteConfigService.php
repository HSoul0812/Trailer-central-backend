<?php
namespace App\Services\Website;


use App\Models\Website\Config\WebsiteConfig;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\EntityRepositoryInterface;

class WebsiteConfigService implements WebsiteConfigServiceInterface
{
    /** @var EntityRepositoryInterface */
    private $websiteEntityRepository;

    /** @var UserRepositoryInterface */
    private $dealerRepository;

    /** @var WebsiteConfigRepositoryInterface */
    private $webConfigRepository;

    /**
     * WebsiteConfigService constructor.
     * @param EntityRepositoryInterface $websiteEntityRepository
     * @param UserRepositoryInterface $dealerRepository
     * @param WebsiteConfigRepositoryInterface $webConfigRepository
     */
    public function __construct(EntityRepositoryInterface $websiteEntityRepository, UserRepositoryInterface $dealerRepository, WebsiteConfigRepositoryInterface $webConfigRepository)
    {
        $this->websiteEntityRepository = $websiteEntityRepository;
        $this->dealerRepository = $dealerRepository;
        $this->webConfigRepository = $webConfigRepository;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getShowroomConfig(array $params): array
    {
        $includeShowRoom = $this->websiteEntityRepository->get($params);
        $showroomDealers = $this->dealerRepository->get($params);
        $showroomUserSeries = $this->webConfigRepository->getValueOfConfig($params['websiteId'], WebsiteConfig::SHOWROOM_USE_SERIES);

        $dealers = unserialize($showroomDealers->showroom_dealers);

        return [
            'include_showroom' => $includeShowRoom,
            'showroom' => $showroomDealers->showroom,
            'showroom_dealers' => $dealers,
            'showroom_user_series' => $showroomUserSeries,
        ];
    }

    public function createShowroomConfig(array $requestData): array
    {
        return $this->webConfigRepository->createOrUpdateShowroomConfig($requestData);
    }

    public function updateShowroomConfig(array $requestData): array
    {
        return $this->webConfigRepository->createOrUpdateShowroomConfig($requestData);
    }
}