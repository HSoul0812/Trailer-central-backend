<?php

declare(strict_types=1);

namespace App\Services\Quickbooks;

use App\Contracts\LoggerServiceInterface;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;

class DealerLocationService implements DealerLocationServiceInterface
{
    /** @var LoggerServiceInterface */
    private $logger;

    /** @var DealerLocationRepositoryInterface */
    private $locationsRepo;

    /** @var QuickbookApprovalRepositoryInterface */
    private $approvalsRepo;

    public function __construct(DealerLocationRepositoryInterface $locationsRepo,
                                QuickbookApprovalRepositoryInterface $approvalsRepo,
                                LoggerServiceInterface $logger)
    {
        $this->logger = $logger;
        $this->locationsRepo = $locationsRepo;
        $this->approvalsRepo = $approvalsRepo;
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
}
