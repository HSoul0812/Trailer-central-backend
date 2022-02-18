<?php

namespace App\Console\Commands\CRM\Leads;

use App\Helpers\SanitizeHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class RemoveBrokenCharacters
 * @package App\Console\Commands\CRM\Leads
 */
class RemoveBrokenCharacters extends Command
{
    /**
     * @var SanitizeHelper
     */
    private $sanitizeHelper;

    /**
     * @var string
     */
    protected $signature = "leads:remove-broken-characters {lead_id}";

    /**
     * @param SanitizeHelper $sanitizeHelper
     */
    public function __construct(SanitizeHelper $sanitizeHelper)
    {
        parent::__construct();
        $this->sanitizeHelper = $sanitizeHelper;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $leadId = $this->argument('lead_id');

        $lead = DB::table('website_lead')->where(['identifier' => $leadId])->first(['comments', 'email_address']);

        if (!$lead) {
            $this->info('The lead doesnt exists');
            return false;
        }

        $sanitizedComments = $this->sanitizeHelper->removeBrokenCharacters($lead->comments);
        $sanitizedEmailAddress = $this->sanitizeHelper->removeBrokenCharacters($lead->email_address);

        DB::table('website_lead')
            ->where(['identifier' => $leadId])
            ->update([
                'comments' => $sanitizedComments,
                'email_address' => $sanitizedEmailAddress,
            ]);

        $this->info('The lead has been successfully updated');

        return true;
    }
}
