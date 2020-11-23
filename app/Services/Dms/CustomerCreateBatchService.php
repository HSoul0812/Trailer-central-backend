<?php


namespace App\Services\Dms;


use App\Exceptions\Dms\CustomerAlreadyExistsException;
use App\Models\CRM\Leads\Lead;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use Illuminate\Support\Facades\Log;

class CustomerCreateBatchService
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function run(array $data)
    {
        if (!$data || count($data) === 0) {
            return;
        }

        foreach ($data as $item) {
            $leadId = $item;
            Log::info("Creating customer from lead [{$leadId}]");

            try {
                $lead = Lead::where('identifier', $leadId)->get()->first();
                $customer = $this->customerRepository->createFromLead($lead);
                if ($customer) {
                    Log::info("Created customer [{$customer->id}]");
                } else {
                    Log::error("Could not create customer from Lead [{$leadId}]");
                }
            } catch (\Exception $e) {
                Log::warning($e->getMessage());
            }
        }
    }
}
