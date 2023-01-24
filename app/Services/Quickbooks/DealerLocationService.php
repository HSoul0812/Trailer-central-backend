<?php

declare(strict_types=1);

namespace App\Services\Quickbooks;

use App\Contracts\LoggerServiceInterface;
use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use App\Jobs\Inventory\ReIndexInventoriesByDealerLocationJob;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\Inventory\Inventory;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DealerLocationService implements DealerLocationServiceInterface
{
    use DispatchesJobs;

    /** @var LoggerServiceInterface */
    private $logger;

    /** @var DealerLocationRepositoryInterface */
    private $locationsRepo;

    /** @var QuickbookApprovalRepositoryInterface */
    private $approvalsRepo;

    /** @var ResponseCacheKeyInterface */
    private $responseCache;

    public function __construct(DealerLocationRepositoryInterface $locationsRepo,
                                QuickbookApprovalRepositoryInterface $approvalsRepo,
                                LoggerServiceInterface $logger,
                                ResponseCacheKeyInterface $responseCache)
    {
        $this->logger = $logger;
        $this->locationsRepo = $locationsRepo;
        $this->approvalsRepo = $approvalsRepo;
        $this->responseCache = $responseCache;
    }

    public function update(int $dealerLocationId): ?QuickbookApproval
    {
        try {
            $location = $this->locationsRepo->get(['dealer_location_id' => $dealerLocationId]);

            if ($location->qboMapping && $location->qboMapping->hasBeenSynced()) {
                $this->logger->info(
                    'Enqueueing the job to update the dealer location name',
                    [
                        'name' => $location->name,
                        'dealer_id' => $location->dealer_id,
                        'dealer_location_id' => $location->dealer_location_id
                    ]
                );

               return $this->approvalsRepo->create([
                    'dealer_id' => $location->dealer_id,
                    'tb_name' => 'dealer_location',
                    'tb_primary_id' => $location->dealer_location_id,
                    'sort_order' => QuickbookApproval::PRIORITY_DEALER_LOCATION,
                    'qb_info' => [
                        'Name' => $location->name,
                        'SubDepartment' => false,
                        'Active' => true
                    ],
                    'qb_id' => $location->qboMapping->quickbooks_id
                ]);
            }
        } catch (\Exception $exception) {
            $this->logger->error(
                'Error enqueueing the job to update the dealer location name',
                [
                    'id' => $dealerLocationId
                ]
            );
        }

        return null;
    }

    /**
     * Reindex the inventory by dealer location, if the cache for inventory is enabled,
     * then it will invalidate cache by dealer id
     *
     * @param  int  $dealerLocationId
     * @return void
     */
    public function invalidateCacheAndReindex(int $dealerLocationId): void
    {
        $location = $this->locationsRepo->get(['dealer_location_id' => $dealerLocationId]);

        $logContext = [
            'name' => $location->name,
            'dealer_id' => $location->dealer_id,
            'dealer_location_id' => $location->dealer_location_id
        ];

        if (Inventory::isCacheInvalidationEnabled()) {
            $this->logger->info(
                'Enqueueing the job to invalidate cache by dealer location',
                $logContext
            );

            $this->dispatch(new InvalidateCacheJob([$this->responseCache->deleteByDealer($location->dealer_id)]));
        }

        $this->logger->info(
            'Enqueueing the job to reindex inventory by dealer location',
            $logContext
        );

        $this->dispatch(new ReIndexInventoriesByDealerLocationJob([$dealerLocationId]));
    }
}
