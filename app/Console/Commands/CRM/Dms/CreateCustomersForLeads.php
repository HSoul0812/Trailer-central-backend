<?php


namespace App\Console\Commands\CRM\Dms;


use App\Exceptions\Dms\CustomerAlreadyExistsException;
use App\Jobs\Dms\CustomerCreateBatchJob;
use App\Models\CRM\Leads\Lead;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use Illuminate\Console\Command;

class CreateCustomersForLeads extends Command
{
    const BATCH_SIZE = 500;

    protected $signature = 'crm:dms:create-customers-for-leads';

    protected $description = 'Create customer objects for leads without customers';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var LeadRepositoryInterface
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
        $leadIds = $this->leadRepository->getLeadsWithoutCustomers();

        $chunk = 0;
        $batch = [];
        foreach ($leadIds as $item) {
            $leadId = $item['identifier'];
            $batch[] = $leadId;
            if (++$chunk >= self::BATCH_SIZE) {
                $job = new CustomerCreateBatchJob($batch);
                dispatch($job);

                $batch = [];
                $chunk = 0;
                $added = false;
            }
        }

        // see if any batch unsent
        if (count($batch) > 0) {
            $job = new CustomerCreateBatchJob($batch);
            dispatch($job);
        }

        return true;
    }
}
