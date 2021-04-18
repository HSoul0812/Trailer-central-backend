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
 * Class AutoAssignJob
 * @package App\Jobs\CRM\Leads
 */
class AutoAssignJob extends Job
{
    use Dispatchable;

    /**
     * @var Lead
     */
    private $lead;
    
    /**
     * AutoAssignJob constructor.
     * 
     * @param Lead $lead
     * @param AutoAssignServiceInterface
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

        // Job Already Has Sales Person?
        if (!empty($this->lead->leadStatus->sales_person_id)) {
            $log->error('Cannot process auto assign; sales person ALREADY assigned to lead!');
            throw new AutoAssignJobSalesPersonExistsException;
        }

        // Process Auto Assign
        $log->info('Handling Auto Assign Manually on Lead #' . $this->lead->identifier);
        $service->autoAssign($this->lead);
        return true;
    }
}