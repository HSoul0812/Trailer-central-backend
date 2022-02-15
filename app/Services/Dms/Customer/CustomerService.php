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
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerService implements CustomerServiceInterface
{
    /**
     * @var string $activeNur
     */
    private $activeNur;

    /**
     * @var object $activeCustomer
     */
    private $activeCustomer;

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


    public function importCSV(array $csvData, int $lineNumber, int $dealerId, int $dealerLocationId, int $popularType, string $category) {
        if ($lineNumber === 1) {
            return;
        }
        list(
            $lastName,
            $firstName,
            $customerNur,
            $address,
            $addressLine2,
            $city,
            $state,
            $zip,
            $phone1,
            $phone2,
            $customerNotes,
            $email,
            $unitYear,
            $unitMake,
            $unitModel,
            $unitSerial,
            $registrationNo,
            $unitType,
            $length,
            $beam,
            $color,
            $hoursMiles,
            $keyCode,
            $unitNotes,
            $datePurchased,
            $lastServiceDate,
            $nextServiceDate,
            $nextServiceType,
            $location,
            $motorYear,
            $motorMake,
            $motorModel,
            $motorHp,
            $motorSerial,
            $prop,
            $trailerYear,
            $trailerMake,
            $trailerSerial,
            ) = $csvData;
        if($this->activeNur !== $customerNur) {
            $this->activeNur = $customerNur;
            try {
                $this->activeCustomer = $this->customerRepository->get([
                    CustomerRepositoryInterface::CONDITION_AND_WHERE => [
                        ['first_name', 'like', $firstName],
                        ['last_name', 'like', $lastName],
                    ],
                    'dealer_id' => $dealerId
                ]);
            } catch(ModelNotFoundException $ex) {
                $this->activeCustomer = $this->customerRepository->create(
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'display_name' => "$firstName $lastName",
                        'email' => $email,
                        'address' => $address,
                        'city' => $city,
                        'dealer_id' => $dealerId,
                        'region' => $state,
                        'postal_code' => $zip,
                        'home_phone' => $phone1,
                        'work_phone' => $phone2,
                        'country' => 'US',
                    ]
                );
            }
        }

        try {
            $inventory = $this->inventoryRepository->get([
                InventoryRepositoryInterface::CONDITION_AND_WHERE => [
                    ['vin', 'LIKE', '%' . $unitSerial . '%']
                ],
                'dealer_id' => $dealerId,
            ]);
        } catch(ModelNotFoundException $ex) {
            $inventory = $this->inventoryRepository->create([
                'dealer_id' => $dealerId,
                'dealer_location_id' => $dealerLocationId,
                'year' => $unitYear,
                'manufacturer' => $unitMake,
                'model' => $unitModel,
                'notes' => $customerNotes,
                'entity_type_id' => $popularType,
                'title' => "$unitYear $unitMake $unitModel",
                'category' => $category,
                'length' => $length,
                'vin' => $unitSerial,
                'attributes' => [
                    ['attribute_id' => 11, 'value' => $color]
                ]
            ]);
        }

        $customerId = $this->activeCustomer->getKey();
        $inventoryId = $inventory->getKey();

        try {
            $this->customerInventoryRepository->get([
                'customer_id' => $customerId,
                'inventory_id' => $inventoryId
            ]);
        } catch(ModelNotFoundException $ex) {
            $this->customerInventoryRepository->create([
                'inventory_id' => $inventoryId,
                'customer_id' => $customerId
            ]);
        }
    }
}
