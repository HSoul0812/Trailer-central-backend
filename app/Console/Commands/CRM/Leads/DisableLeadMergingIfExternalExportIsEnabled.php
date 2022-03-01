<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Console\Command;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Models\Website\Config\WebsiteConfig;

/**
 * Class DisableLeadMergingIfExternalExportIsEnabled
 * @package App\Console\Commands\CRM\Leads
 */
class DisableLeadMergingIfExternalExportIsEnabled extends Command
{

    /**
     * @var string
     */
    protected $signature = "leads:disable-lead-merging-if-external-export-is-enabled";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(LeadEmailRepositoryInterface $leadEmailRepo, WebsiteConfigRepositoryInterface $websiteConfigRepo)
    {        
        $leadEmails = $leadEmailRepo->getAll([]);

        foreach($leadEmails as $leadEmail) {
            if ($leadEmail->dealer->website) {
                $this->info("Disabling lead merge for {$leadEmail->dealer->dealer_id}");
                $websiteConfigRepo->setValue($leadEmail->dealer->website->id, WebsiteConfig::LEADS_MERGE_ENABLED, 0);  
            }                      
        }
    }
}
