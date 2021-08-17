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
     * @var string $active_nur
     */
    private $active_nur;

    /**
     * @var object $active_customer
     */
    private $active_customer;

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
     * Create a new command instance.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param CustomerInventoryRepositoryInterface $customerInventoryRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        InventoryRepositoryInterface $inventoryRepository,
        CustomerInventoryRepositoryInterface $customerInventoryRepository
    )
    {
        $this->customerRepository = $customerRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->customerInventoryRepository = $customerInventoryRepository;
    }


    public function importCSV(array $csvData, int $lineNumber, int $dealer_id, int $dealer_location_id, int $popular_type, string $category) {
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
        if($this->active_nur !== $customer_nur) {
            $this->active_nur = $customer_nur;
            $customer = $this->customerRepository->firstByNameAndDealer(
                $first_name, $last_name, $dealer_id
            );
            if(!$customer) {
                $this->active_customer = $this->customerRepository->create(
                    [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'display_name' => "$first_name $last_name",
                        'email' => $email,
                        'address' => $address,
                        'city' => $city,
                        'dealer_id' => $dealer_id,
                        'region' => $state,
                        'postal_code' => $zip,
                        'home_phone' => $phone1,
                        'work_phone' => $phone2,
                        'country' => 'US',
                    ]
                );
            } else {
                $this->active_customer = $customer;
            }
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
                'vin' => $unit_serial,
                'attributes' => [
                    ['attribute_id' => 11, 'value' => $color]
                ]
            ]);
        }
        $customer_id = $this->active_customer->getKey();
        $inventory_id = $inventory->getKey();

        $customer_inventory = $this->customerInventoryRepository->get([
            'customer_id' => $customer_id,
            'inventory_id' => $inventory_id
        ]);
        if(!$customer_inventory) {
            $this->customerInventoryRepository->create([
                'inventory_id' => $inventory->getKey(),
                'customer_id' => $this->active_customer->getKey()
            ]);
        }
    }
}
