<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Models\User\NewUser;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read SalesPerson $sales
 * @property-read Lead $leads
 * @property-read AuthToken $authToken
 * @property-read array<LeadStatus> $missingStatus
 * @property-read array<LeadStatus> $createdStatus
 */
class StatusSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * @var Website
     */
    private $website;

    /**
     * @var SalesPerson
     */
    private $sales;

    /**
     * @var Lead[]
     */
    private $leads;

    /**
     * @var LeadStatus[]
     */
    private $missingStatus = [];

    /**
     * @var LeadStatus[]
     */
    private $createdStatus = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $this->user = factory(NewUser::class)->create();
        $this->sales = factory(SalesPerson::class)->create(['user_id' => $this->user->user_id]);
    }

    public function seed(): void
    {
        $salesId = $this->sales->getKey();

        $seeds = [
            ['source' => ''],
            ['source' => 'Facebook - Podium'],
            ['source' => '', 'sales_id' => $salesId],
            ['source' => 'RVTrader.com', 'sales_id' => $salesId],
            ['source' => 'TrailerCentral', 'action' => 'create'],
            ['source' => '', 'action' => 'create'],
            ['source' => 'HorseTrailerWorld', 'sales_id' => $salesId, 'action' => 'create'],
            ['source' => '', 'sales_id' => $salesId, 'action' => 'create']
        ];

        collect($seeds)->each(function (array $seed): void {
            $lead = factory(Lead::class)->create([
                'dealer_id' => $this->dealer->getKey(),
                'website_id' => $this->website->getKey()
            ]);
            $leadId = $lead->getKey();
            $this->leads[$leadId] = $lead;

            // Create Status
            if(isset($seed['action']) && $seed['action'] === 'create') {
                // Make Status
                $status = factory(LeadStatus::class)->create([
                    'tc_lead_identifier' => $leadId,
                    'source' => $seed['source'],
                    'sales_person_id' => $seed['sales_id'] ?? 0
                ]);

                $this->createdStatus[$leadId] = $status;
                return;
            }

            // Make Status
            $status = factory(LeadStatus::class)->make([
                'tc_lead_identifier' => $leadId,
                'source' => $seed['source'],
                'sales_person_id' => $seed['sales_id'] ?? 0
            ]);

            $this->missingStatus[$leadId] = $status;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $userId = $this->user->getKey();

        // Database clean up
        if(!empty($this->leads) && count($this->leads)) {
            foreach($this->leads as $lead) {
                $leadId = $lead->identifier;
                LeadStatus::where('tc_lead_identifier', $leadId)->delete();
                Lead::destroy($leadId);
            }
        }

        // Delete CRM User Related Models
        SalesPerson::where('user_id', $userId)->delete();
        NewUser::destroy($userId);

        // Delete Dealer Related Models
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        User::destroy($dealerId);
    }
}
