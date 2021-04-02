<?php

namespace App\Jobs\CRM\Leads;

use App\Jobs\Job;
use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
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
     * @var App\Services\CRM\Leads\AutoAssignService
     */
    private $service;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;
    
    /**
     * AutoAssignJob constructor.
     * 
     * @param Lead $lead
     * @param AutoAssignServiceInterface
     */
    public function __construct(Lead $lead, AutoAssignServiceInterface $service)
    {
        $this->lead = $lead;
        $this->service = $service;

        // Initialize Logger
        $this->log = Log::channel('autoassign');
    }

    public function handle()
    {
        // Job Doesn't Exist?
        if (empty($this->lead)) {
            throw new AutoAssignJobMissingLeadException;
        }

        // Job Already Has Sales Person?
        if (!empty($this->lead->leadStatus->sales_person_id)) {
            throw new AutoAssignJobSalesPersonExistsException;
        }

        // Process Auto Assign
        $this->log->info('Handling Auto Assign Manually on Lead #' . $this->lead->identifier);
        return $this->service->autoAssign($lead);
    }
}
