<?php


namespace App\Console\Commands\CRM\Dms;


use App\Jobs\Dms\CustomerCreateBatchJob;
use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepository;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use Illuminate\Console\Command;

class CreateCustomersForLeads extends Command
{
    const BATCH_SIZE = 500;

    protected $signature = 'crm:dms:create-customers-for-leads';

    protected $description = 'Create customer objects for leads without customers';

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
        $this->leadRepository->getLeadsWithoutCustomers(function($leads) {
            $batch = [];
            foreach ($leads as $item) {
                $leadId = $item->identifier;
                $batch[] = $leadId;
            }
//            $job = new CustomerCreateBatchJob($batch);
//            dispatch($job);
        });

        return true;
    }
}
