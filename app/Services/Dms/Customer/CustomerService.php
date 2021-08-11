<?php
namespace App\Services\Dms\Customer;

use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\Dms\Customer\InventoryRepository as CustomerInventoryRepository;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface as CustomerInventoryRepositoryInterface;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationRepository;
use App\Repositories\User\DealerLocationRepositoryInterface;

class CustomerService implements CustomerServiceInterface
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var InventoryRepository
     */
    private $inventoryRepository;

    /**
     * @var CustomerInventoryRepository
     */
    private $customerInventoryRepository;

    /**
     * @var DealerLocationRepository
     */
    private $dealerLocationRepository;

    /**
     * Create a new command instance.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param CustomerInventoryRepositoryInterface $customerInventoryRepository
     * @param DealerLocationRepositoryInterface $dealerLocationRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        InventoryRepositoryInterface $inventoryRepository,
        CustomerInventoryRepositoryInterface $customerInventoryRepository,
        DealerLocationRepositoryInterface $dealerLocationRepository
    )
    {
        $this->customerRepository = $customerRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->customerInventoryRepository = $customerInventoryRepository;
        $this->dealerLocationRepository = $dealerLocationRepository;
    }


    public function importCSV(array $csvData, int $lineNumber, ?string &$active_nur, &$active_customer, int $dealer_id, int $dealer_location_id, int $popular_type, string $category) {
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

        print_r($customer_nur);
        if($active_nur !== $customer_nur) {
            $active_nur = $customer_nur;
            $customers = $this->customerRepository->search(
                ['query' => "first_name:\"Alberto\" AND last_name:\"Carillo\""], $dealer_id
            );
            print_r("first_name:\"$first_name\" AND last_name:\"$last_name\"");
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
            print_r('first_name' . $active_customer->first_name);
            print_r('last_name' . $active_customer->last_name);
        }

        $inventory = $this->inventoryRepository->findOneByVinAndDealerId($unit_serial, $dealer_id);
        if(!$inventory) {
            $inventory = $this->inventoryRepository->create([
                'dealer_id' => $dealer_id,
                'dealer_location_id' => $dealer_location_id,
                'year' => $unit_year,
                'manufacturer' => $unit_make,
                'model' => $unit_model,
                'notes' => $customer_notes,
                'entity_type_id' => $popular_type,
                'title' => "$unit_year $unit_make $unit_model",
                'category' => $category,
                'length' => $length,
                'attributes' => [
                    ['attribute_id' => 11, 'value' => $color]
                ]
            ]);
        }

        $customer_id = $active_customer->id;
        $inventory_id = $inventory->inventory_id;

        $customer_inventory = $this->customerInventoryRepository->findFirstByCustomerAndInventory($customer_id, $inventory_id);
        if(!$customer_inventory) {
            $this->customerInventoryRepository->create([
                'inventory_id' => $inventory->inventory_id,
                'customer_id' => $active_customer->id,
            ]);
        }
    }
}
