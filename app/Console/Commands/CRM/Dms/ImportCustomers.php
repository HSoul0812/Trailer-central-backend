<?php

namespace App\Console\Commands;

use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
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
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var InventoryRepository
     */
    private $inventoryRepository;

    /**
     * Create a new command instance.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        InventoryRepositoryInterface $inventoryRepository
    )
    {
        parent::__construct();
        $this->customerRepository = $customerRepository;
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

        $entity_type = DB::table('inventory')
            ->select(DB::raw('count(*) as type_count, entity_type_id'))
            ->where('dealer_id', $dealer_id)
            ->groupBy('entity_type_id')
            ->orderBy('type_count', 'desc')
            ->first();

        $popular_type = 1;
        if($entity_type) {
            $popular_type = $entity_type['entity_type_id'];
        }

        if($popular_type === 1) {
            $category = 'atv';
        } else {
            $category = '';
        }

        $inventories = [];

        $active_nur = null;
        $active_customer = null;

        $this->streamCsv(function ($csvData, $lineNumber) use (&$active_nur, &$active_customer, $dealer_id, $popular_type, $category) {
            if ($lineNumber === 1) {
                return;
            }
            list(
                $last_name,
                $first_name,
                $customer_nur,
                $address,
                $address_line2,
                $city,
                $state,
                $zip,
                $phone1,
                $phone2,
                $customer_notes,
                $email,
                $unit_year,
                $unit_make,
                $unit_model,
                $unit_serial,
                $registration_no,
                $unit_type,
                $length,
                $beam,
                $color,
                $hours_miles,
                $key_code,
                $unit_notes,
                $date_purchased,
                $last_service_date,
                $next_service_date,
                $next_service_type,
                $location,
                $motor_year,
                $motor_make,
                $motor_model,
                $motor_hp,
                $motor_serial,
                $prop,
                $trailer_year,
                $trailer_make,
                $trailer_serial,
            ) = $csvData;

            if($active_nur !== $customer_nur) {
                $active_nur = $customer_nur;
                $customers = $this->customerRepository->search("first_name:$first_name AND last_name:$last_name", $dealer_id);
                if($customers->isEmpty()) {
                    $active_customer = $this->customerRepository->create(
                        [
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'address' => $address,
                            'city' => $city,
                            'region' => $state,
                            'postal_code' => $zip,
                            'home_phone' => $phone1,
                            'work_phone' => $phone2,
                            'country' => 'US',
                        ]
                    );
                } else {
                    $active_customer = $customers[0];
                }
            }

            $inventory = $this->inventoryRepository->findOneByVinAndDealerId($unit_serial, $dealer_id);
            if(!$inventory) {
                $this->inventoryRepository->create([
                    'year' => $unit_year,
                    'manufacturer' => $unit_make,
                    'model' => $unit_model,
                    'notes' => $customer_notes,
                    'entity_type_id' => $popular_type,
                    'title' => "$unit_year $unit_make $unit_model",
                    'category' => $category,
                    'length' => $length
                ]);
            }
        });
    }
}
