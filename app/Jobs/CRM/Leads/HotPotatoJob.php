<?php

namespace App\Jobs\CRM\Leads;

use App\Jobs\Job;
use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Exceptions\CRM\Leads\AutoAssignJobMissingLeadException;
use App\Exceptions\CRM\Leads\AutoAssignJobSalesPersonExistsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class HotPotatoJob
 * @package App\Jobs\CRM\Leads
 */
class HotPotatoJob extends Job
{
    use Dispatchable;

    /**
     * @var Lead
     */
    private $lead;
    
    /**
     * HotPotatoJob constructor.
     * 
     * @param Lead $lead
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Handle Auto Assign Job
     * 
     * @param AutoAssignServiceInterface $service
     * @throws AutoAssignJobMissingLeadException
     * @throws AutoAssignJobSalesPersonExistsException
     * @return boolean
     */
    public function handle(AutoAssignServiceInterface $service)
    {
        // Initialize Log
        $log = Log::channel('autoassign');

        // Process Hot Potato
        $log->info('Handling Hot Potato Manually on Lead #' . $this->lead->identifier);
        $service->hotPotato($this->lead);
        return true;
    }
}