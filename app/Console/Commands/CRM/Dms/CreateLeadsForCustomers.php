<?php

namespace App\Console\Commands\CRM\Dms;

use App\Jobs\Dms\CustomerCreateBatchJob;
use App\Models\CRM\User\Customer;
use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepository;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CreateLeadsForCustomers extends Command
{
    protected $signature = 'crm:dms:create-leads-for-customers';

    protected $description = 'Create lead objects for customers without leads';

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var LeadRepository
     */
    private $leadRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository, LeadRepositoryInterface $leadRepository)
    {
        parent::__construct();

        $this->customerRepository = $customerRepository;
        $this->leadRepository = $leadRepository;
    }

    public function handle()
    {
        $this->customerRepository->getCustomersWithoutLeads(
            // The main callback
            function (Collection $customers) {
                /** @var Customer $customer */
                foreach ($customers as $customer) {
                    try {
                        $this->leadRepository->createLeadFromCustomer($customer);

                        $this->info("Created a lead for the customer id $customer->id!");
                    } catch (Exception $exception) {
                        $this->error("Failed to create a lead: {$exception->getMessage()}");
                    }
                }
            },

            // Select only some columns
            ['id', 'dealer_id', 'first_name', 'middle_name', 'last_name', 'email', 'cell_phone', 'address', 'city', 'postal_code', 'website_lead_id'],

            // Eager load
            [
                'dealer' => function (BelongsTo $query) {
                    $query->select(['dealer_id']);
                },
                'dealer.website' => function (HasOne $query) {
                    $query->select(['id', 'dealer_id', 'domain']);
                }
            ]
        );

        return 0;
    }
}
