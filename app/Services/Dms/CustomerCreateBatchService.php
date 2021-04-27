<?php


namespace App\Services\Dms;


use App\Models\CRM\Leads\Lead;
use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use Illuminate\Support\Facades\Log;

class CustomerCreateBatchService
{
    /**
     * @var CustomerRepository
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

                if (!$customer) {
                    Log::error("Could not create customer from Lead [{$leadId}]");
                    continue;
                }

                Log::info("[{$customer->dealer_id}] Created/used existing customer [{$customer->id}] [{$customer->last_name}, {$customer->first_name}]");
                $lead->customer_id = $customer->id;
                $lead->save();

            } catch (\Exception $e) {
                Log::warning($e->getMessage());
            }
        }
    }
}
