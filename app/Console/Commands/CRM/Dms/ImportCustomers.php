<?php

namespace App\Console\Commands\CRM\Dms;

use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\Dms\Customer\CustomerServiceInterface;
use App\Traits\StreamCSVTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
     * Create a new command instance.
     * @param CustomerServiceInterface $customerService
     * @param DealerLocationRepositoryInterface $dealerLocationRepository
     */
    public function __construct(
        DealerLocationRepositoryInterface  $dealerLocationRepository,
        CustomerServiceInterface $customerService
    )
    {
        parent::__construct();
        $this->dealerLocationRepository = $dealerLocationRepository;
        $this->customerService = $customerService;
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
        $dealer_location_id = $this->dealerLocationRepository->findFirstByDealerId($dealer_id)->dealer_location_id;
        $entity_type = DB::table('inventory')
            ->select(DB::raw('count(*) as type_count, entity_type_id'))
            ->where('dealer_id', $dealer_id)
            ->groupBy('entity_type_id')
            ->orderBy('type_count', 'desc')
            ->first();

        $popular_type = 1;
        if($entity_type) {
            $popular_type = $entity_type->entity_type_id;
        }
        $this->info('Inventory Type: ' . $popular_type);
        if($popular_type === 1) {
            $category = 'atv';
        } else {
            $category = '';
        }
        $this->info('Inventory Category: ' . $category);
        $active_nur = null;
        $active_customer = null;

        $this->streamCsv(function ($csvData, $lineNumber) use (&$active_nur, &$active_customer, $dealer_id, $dealer_location_id, $popular_type, $category) {
            $this->info('Importing line number: ' . $lineNumber);
            $this->customerService->importCSV($csvData, $lineNumber, $active_nur, $active_customer, $dealer_id, $dealer_location_id, $popular_type, $category);
            $this->info('Imported line number: ' . $lineNumber);
        });
        return 0;
    }
}
