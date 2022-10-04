<?php

namespace App\Jobs\CRM\Leads;

use App\Jobs\Job;
use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Exceptions\CRM\Leads\AutoAssignJobMissingLeadException;
use App\Exceptions\CRM\Leads\AutoAssignJobSalesPersonExistsException;
use Illuminate\Support\Collection;
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
     * @var Collection<array{key: value}>
     */
    private $settings;
    
    /**
     * HotPotatoJob constructor.
     * 
     * @param Lead $lead
     * @param Collection<array{key: value}> $settings
     */
    public function __construct(Lead $lead, Collection $settings)
    {
        $this->lead = $lead;
        $this->settings = $settings;
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
        $log = Log::channel('hotpotato');

        // Process Hot Potato
        $log->info('Handling Hot Potato Manually on Lead #' . $this->lead->identifier);
        $service->hotPotato($this->lead, $this->settings);
        return true;
    }
}