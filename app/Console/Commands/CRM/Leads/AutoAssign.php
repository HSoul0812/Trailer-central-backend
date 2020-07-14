<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Console\Command;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Traits\MailHelper;
use Carbon\Carbon;

class AutoAssign extends Command
{
    use MailHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:assign:auto {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Assign leads to SalesPeople.';

    /**     
     * @var App\Repositories\CRM\Leads\LeadRepository
     */
    protected $leadRepository;

    /**     
     * @var App\Repositories\CRM\User\SalesPersonRepository
     */
    protected $inventoryRepository;

    /**
     * @var string
     * @var datetime
     */
    protected $timezone = 'America/Indiana/Indianapolis';
    protected $datetime = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LeadRepositoryInterface $leadRepo, SalesPersonRepositoryInterface $salesPersonRepo)
    {
        parent::__construct();

        $this->leadRepository = $leadRepo;
        $this->salesPersonRepository = $salesPersonRepo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Dealer ID
        $dealerId = $this->argument('dealer');

        // Initialize Time
        date_default_timezone_set($this->timezone);
        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone($this->timezone));

        // Get Dealers With Unassigned Leads
        $dealers = NewDealerUser::has('salespeopleEmails')->with('crmUser')->get();
        var_dump($dealers);
        die;
        foreach($dealers as $dealer) {
            // Loop Leads for Current Dealer
            foreach($dealers->leadsUnassigned as $lead) {
                // Get Vars
                $leadType = $this->salesPersonRepository->findSalesType($lead->lead_type);
                $dealerLocationId = $lead->dealer_location_id;

                // Find Next Salesperson
                $salesPerson = $this->salesPersonRepository->findNextSalesPerson($dealerId, $newestSalesPersonId, $dealerLocationId, $leadType);
                if(empty($salesPerson->id)) {
                    // TO DO: Log Inability to Find Salesperson, Put Off for Later!
                    continue;
                }

                // Initialize Next Contact Date
                $nextDay = date("d") + 1;
                $nextContactTime = mktime(9, 0, 0, $this->datetime->format("n"), $nextDay);
                $nextContactDate = new \DateTime(date("Y:m:d H:i:s", $nextContactTime), new \DateTimeZone($this->timezone));

                // Set Salesperson to Lead
                $this->leadRepository->update([
                    'id' => $lead->identifier,
                    'sales_person_id' => $salesPerson->id,
                    'next_contact_date' => $nextContactDate
                ]);

                // Send Sales Email
                if(!empty($dealer->crmUser->enable_assign_notification)) {
                    // Send Sales Email
                    $this->sendSalesEmail($salesPerson, $lead->identifier);
                }
            }
        }
    }

    private function getUnassignedDealers($dealerId = 0) {
        // Create Parameters for Unassigned Leads
        $params = array(
            'per_page' => 'all'
        );
        if(!empty($dealerId)) {
            $params['dealer_id'] = $dealerId;
        }

        // Get Leads
        $leads = $this->leadRepository->getAllUnassigned($params);

        // Initialize Dealers Array
        $dealers = array();
        foreach($leads as $lead) {
            if(!empty($lead->dealer_id)) {
                // Dealer Doesn't Exist Yet?!
                if(!isset($dealers[$lead->dealer_id])) {
                    $dealers[$lead->dealer_id] = array();
                }

                // Append to Array
                $dealers[$lead->dealer_id][] = $lead;
            }
        }

        // Return Dealers
        return $dealers;
    }

    /**
     * Send Email to Sales Person for Auto Assign
     * 
     * @param App\Models\CRM\User\SalesPerson $salesPerson
     * @param type $leadId
     */
    private function sendSalesEmail(SalesPerson $salesPerson, $leadId) {

        // Send Email
        Mail::to($customer["email"] ?? "" )->send(
            new AutoAssignEmail([
                'date' => Carbon::now()->toDateTimeString(),
                'replyToEmail' => $user->email ?? "",
                'replyToName' => "{$user->crmUser->first_name} {$user->crmUser->last_name}",
                'subject' => $subject,
                'body' => $body,
                'attach' => $attach,
                'id' => $uniqueId
            ])
        );
    }
}
