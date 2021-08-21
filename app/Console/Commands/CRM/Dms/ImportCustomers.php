<?php

namespace App\Console\Commands\CRM\Dms;

use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\Dms\Customer\CustomerServiceInterface;
use App\Traits\StreamCSVTrait;
use Illuminate\Console\Command;

class ImportCustomers extends Command
{
    use StreamCSVTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:import-customers {dealer_id} {s3_bucket} {s3_key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import customers from s3';

    /**
     * @var CustomerServiceInterface $customerService
     */
    private $customerService;

    /**
     * @var DealerLocationRepositoryInterface $dealerLocationRepository
     */
    private $dealerLocationRepository;

    /**
     * @var InventoryRepositoryInterface $inventoryRepository
     */
    private $inventoryRepository;

    /**
     * Create a new command instance.
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param CustomerServiceInterface $customerService
     * @param DealerLocationRepositoryInterface $dealerLocationRepository
     */
    public function __construct(
        DealerLocationRepositoryInterface  $dealerLocationRepository,
        CustomerServiceInterface $customerService,
        InventoryRepositoryInterface $inventoryRepository
    )
    {
        parent::__construct();
        $this->dealerLocationRepository = $dealerLocationRepository;
        $this->customerService = $customerService;
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dealer_id = $this->argument('dealer_id');
        $this->s3Bucket = $this->argument('s3_bucket');
        $this->s3Key = $this->argument('s3_key');

        $dealer_location_id = $this->dealerLocationRepository->get(['dealer_id' => $dealer_id])->getKey();
        $popularInventory = $this->inventoryRepository->getPopularInventory($dealer_id);

        $popularType = 1;
        if($popularInventory) {
            $popularType = $popularInventory->entity_type_id;
        }
        $popularCategory = $popularInventory->category;

        $this->info('Inventory Type: ' . $popularType);
        $this->info('Inventory Category: ' . $popularCategory);

        $this->streamCsv(function ($csvData, $lineNumber) use ($dealer_id, $dealer_location_id, $popularType, $popularCategory) {
            $this->info('Importing line number: ' . $lineNumber);
            $this->customerService->importCSV($csvData, $lineNumber, $dealer_id, $dealer_location_id, $popularType, $popularCategory);
            $this->info('Imported line number: ' . $lineNumber);
        });
        return 0;
    }
}
