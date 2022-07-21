<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Website\Website;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Illuminate\Support\Collection;

class ExtraWebsiteConfigService implements ExtraWebsiteConfigServiceInterface
{
    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var UserRepositoryInterface */
    private $dealerRepository;

    /** @var ShowroomRepositoryInterface */
    private $showroomRepository;

    /** @var LoggerServiceInterface */
    private $logger;

    public function __construct(WebsiteRepositoryInterface $websiteRepository,
                                UserRepositoryInterface $dealerRepository,
                                ShowroomRepositoryInterface $showroomRepository,
                                LoggerServiceInterface $logger)
    {
        $this->websiteRepository = $websiteRepository;
        $this->showroomRepository = $showroomRepository;
        $this->dealerRepository = $dealerRepository;
        $this->logger = $logger;
    }

    public function getAll(int $websiteId): Collection
    {
        /** @var Website $website */
        $website = $this->websiteRepository->get(['id' => $websiteId]);

        $dealer = $this->dealerRepository->get(['dealer_id' => $website->dealer_id]);

        $showroomDealers = [];

        try{
            $showroomDealers = array_filter(unserialize($dealer->showroom_dealers));
        } catch (\Exception $exception){
            $this->logger->error('`ExtraWebsiteConfigService::getAll` has failed to unserialize `showroom_dealers`');
        }

        return collect([
            'include_showroom' => (bool)$dealer->showroom,
            'showroom_dealers' => $showroomDealers,
            'available_showroom_dealers' => $this->showroomRepository->distinctByManufacturers(),
            'global_filter' => $website->type_config
        ]);
    }

    public function createOrUpdate(array $params): Collection
    {
        throw new NotImplementedException('Not implemented yed');
    }
}
