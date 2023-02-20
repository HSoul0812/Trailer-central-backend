<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\Contracts\LoggerServiceInterface;
use App\Models\User\User;
use App\Models\Website\Website;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Website\EntityRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;

class ExtraWebsiteConfigService implements ExtraWebsiteConfigServiceInterface
{
    private const ENTITY_SHOWROOM_TYPE = 9;
    private const ENTITY_SHOWROOM_DEFAULT_VALUES = [
        'entity_type' => self::ENTITY_SHOWROOM_TYPE,
        'parent' => 0,
        'title' => 'Showroom',
        'url_path' => 'showroom',
        'sort_order' => 99,
        'in_nav' => 1,
        'is_active' => 1,
        'template' => '1column'
    ];

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var UserRepositoryInterface */
    private $dealerRepository;

    /** @var ShowroomRepositoryInterface */
    private $showroomRepository;

    /** @var EntityRepositoryInterface */
    private $entityRepository;

    /** @var Connection */
    private $connection;

    /** @var LoggerServiceInterface */
    private $logger;

    public function __construct(WebsiteRepositoryInterface  $websiteRepository,
                                UserRepositoryInterface     $dealerRepository,
                                ShowroomRepositoryInterface $showroomRepository,
                                EntityRepositoryInterface   $entityRepository,
                                Connection                  $connection,
                                LoggerServiceInterface      $logger)
    {
        $this->websiteRepository = $websiteRepository;
        $this->showroomRepository = $showroomRepository;
        $this->entityRepository = $entityRepository;
        $this->dealerRepository = $dealerRepository;
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function getAllByWebsiteId(int $websiteId): Collection
    {
        $website = $this->getWebsiteById($websiteId);
        $mainDealer = $this->getDealerById($website->dealer_id);

        $showroomDealers = $mainDealer->getShowroomDealers() ?? [];
        try {
            $dealersIds = $website->getFilterValue('dealer_id') ?? [];
            foreach ($dealersIds as $dealerId) {
                $dealer = $this->getDealerById((int)$dealerId);
                if ($dealers = $dealer->getShowroomDealers()) {
                    $showroomDealers = array_merge($showroomDealers, $dealers);
                }
            }
            $showroomDealers = array_unique($showroomDealers);
        } catch (\Exception $exception) {
            $this->logger->error('`ExtraWebsiteConfigService::getAll` has failed to unserialize `showroom_dealers`');
        }

        return collect([
            'include_showroom' => (bool)$mainDealer->showroom,
            'showroom_dealers' => $showroomDealers,
            'available_showroom_dealers' => $this->showroomRepository->distinctByManufacturers(),
            'global_filter' => $website->type_config
        ]);
    }

    /**
     * @param int $websiteId
     * @param array{include_showroom: boolean, showroom_dealers: array<string>, global_filter: string} $params
     * @throws \Exception when something goes wrong at saving time
     */
    public function updateByWebsiteId(int $websiteId, array $params): void
    {
        $website = $this->getWebsiteById($websiteId);
        $dealer = $this->getDealerById($website->dealer_id);

        try {
            $this->connection->beginTransaction();

            if (isset($params['include_showroom'])) {
                $dealer->showroom = (int)$params['include_showroom'];

                // this has disabled on the legacy dashboard codebase, but it seems the developer intention is clear
                // so, when we should need to enable this again then this will be already implemented
                // $this->updateWebsiteEntityByWebsiteId($website->id, $dealer->showroom);
            }

            if (isset($params['showroom_dealers'])) {
                $dealer->showroom_dealers = serialize($params['showroom_dealers']);
                $this->entityRepository->updateConfig($websiteId, ['manufacturers' => $params['showroom_dealers']]);
            }

            $dealer->save();

            if (array_key_exists('global_filter', $params)) {
                $website->type_config = (string)$params['global_filter'];
                $website->save();
            }

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            $this->logger->error('`ExtraWebsiteConfigService::updateByWebsiteId` ' . $exception->getMessage());

            throw $exception;
        }
    }

    private function getWebsiteById(int $websiteId): Website
    {
        return $this->websiteRepository->get(['id' => $websiteId]);
    }

    private function getDealerById(int $dealerId): User
    {
        return $this->dealerRepository->get(['dealer_id' => $dealerId]);
    }

    /**
     * @param int $websiteId
     * @param int $showRoom
     * @return void
     * @throws \PDOException when something goes wrong at saving time
     */
    private function updateWebsiteEntityByWebsiteId(int $websiteId, int $showRoom): void
    {
        $this->connection->statement(
            'DELETE FROM website_entity WHERE website_id = :website_id AND entity_type = :entity_type',
            ['website_id' => $websiteId, 'entity_type' => self::ENTITY_SHOWROOM_TYPE]
        );

        if ($showRoom) {
            $this->connection->statement('INSERT INTO website_entity (
                                    entity_type,
                                    website_id,
                                    parent,
                                    title,
                                    url_path,
                                    date_created,
                                    date_modified,
                                    sort_order,
                                    in_nav,
                                    is_active,
                                    template
                                )
                                VALUES (
                                    :entity_type,
                                    :website_id,
                                    :parent,
                                    :title,
                                    :url_path,
                                    now(),
                                    now(),
                                    :sort_order,
                                    :in_nav,
                                    :is_active,
                                    :template
                                )',
                array_merge(['website_id' => $websiteId], self::ENTITY_SHOWROOM_DEFAULT_VALUES)
            );
        }
    }
}
