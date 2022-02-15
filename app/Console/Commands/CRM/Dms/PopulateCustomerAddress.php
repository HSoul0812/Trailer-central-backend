<?php


namespace App\Console\Commands\CRM\Dms;

use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use Illuminate\Console\Command;
use App\Models\CRM\User\Customer;

class PopulateCustomerAddress extends Command
{
    const BATCH_SIZE = 500;

    protected $signature = 'crm:dms:populate-customer-address {dealerId?}';

    protected $description = 'Populate customers address based on shipping address';

    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    
    /**     
     * @var int 
     */
    private $dealerId;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        parent::__construct();
        $this->customerRepository = $customerRepository;
    }

    public function handle()
    {
        $dealerId = $this->argument('dealerId');
        
        $getCustomerQuery = Customer::where(function ($query) {
               $query->where('address', '')
                     ->orWhereNull('address');
           })->where(function($query) {
               $query->where('shipping_address', '!=', '')
                        ->whereNotNull('shipping_address');
           });
           
        if ($dealerId) {
            $getCustomerQuery->where('dealer_id', $dealerId);
        }
        
        $getCustomerQuery->chunk(500, function($customers) {
            foreach($customers as $customer) {
                if (empty($customer->shipping_address)) {
                    continue;
                }

                $customer->address = $customer->shipping_address;

                if (empty($customer->city)) {
                    $customer->city = $customer->shipping_city;
                }

                if (empty($customer->region)) {
                    $customer->region = $customer->shipping_region;
                }

                if (empty($customer->postal_code)) {
                    $customer->postal_code = $customer->shipping_postal_code;
                }

                if (empty($customer->country)) {
                    $customer->country = $customer->shipping_country;
                }

                if (empty($customer->county)) {
                    $customer->county = $customer->shipping_county;
                }

                $this->info("Updating customer ID {$customer->id} with address: {$customer->address}, city: {$customer->city}, region: {$customer->region}, postal_code: {$customer->postal_code}, country: {$customer->country}, county: {$customer->county}");                
                $customer->save();
            }
        });

        return true;
    }
}
