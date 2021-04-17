<?php

namespace App\Jobs\CRM\Leads;

use App\Jobs\Job;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Exceptions\CRM\Leads\AutoAssignJobMissingLeadException;
use App\Exceptions\CRM\Leads\AutoAssignJobSalesPersonExistsException;
use Illuminate\Support\Facades\Log;

/**
 * Class AutoAssignJob
 * @package App\Jobs\CRM\Leads
 */
class AutoAssignJob extends Job
{
    /**
     * @var int
     */
    private $leadId;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;
    
    /**
     * AutoAssignJob constructor.
     * 
     * @param int $leadId
     * @param AutoAssignServiceInterface
     */
    public function __construct(int $leadId)
    {
        $this->leadId = $leadId;

        // Initialize Logger
        $this->log = Log::channel('autoassign');
    }

    /**
     * Handle Auto Assign Job
     * 
     * @param AutoAssignServiceInterface $service
     * @throws AutoAssignJobMissingLeadException
     * @throws AutoAssignJobSalesPersonExistsException
     * @return boolean
     */
    public function handle(LeadRepositoryInterface $repository, AutoAssignServiceInterface $service)
    {
        // Get Lead From Repository
        $lead = $repository->get(['id' => $this->leadId]);

        // Lead Already Has Sales Person?
        if (!empty($lead->leadStatus->sales_person_id)) {
            $this->log->error('Cannot process auto assign; sales person ALREADY assigned to lead!');
            throw new AutoAssignJobSalesPersonExistsException;
        }

        // Process Auto Assign
        $this->log->info('Handling Auto Assign Manually on Lead #' . $lead->identifier);
        $service->autoAssign($lead);
        return true;
    }
}