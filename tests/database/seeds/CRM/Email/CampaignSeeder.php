<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Email;

use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\CampaignSent;
use App\Models\CRM\Email\Template;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
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
 * @property-read array<Lead> $leads
 * @property-read array<Campaign> $campaigns
 * @property-read array<CampaignSent> $campaignsSent
 * @property-read array<CampaignSent> $campaignsUnsent
 */
class CampaignSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Lead[]
     */
    private $leads;

    /**
     * @var Campaigns[]
     */
    private $createdCampaigns = [];

    /**
     * @var Campaigns[]
     */
    private $missingCampaigns = [];

    /**
     * @var CampaignSent[]
     */
    private $campaignsSent = [];

    /**
     * @var CampaignSent[]
     */
    private $campaignsUnsent = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->user = factory(NewUser::class)->create(['user_id' => $this->dealer->dealer_id]);
    }

    public function seed(): void
    {
        $seeds = [
            ['name' => 'Test Campaign 1', 'action' => 'create'],
            ['name' => 'Test Campaign 2', 'action' => 'create'],
            ['name' => 'Test Campaign 3', 'action' => 'create'],
            ['name' => 'Test Campaign 4'],
            ['name' => 'Test Campaign 5'],
            ['name' => 'Test Campaign 6'],
        ];

        collect($seeds)->each(function (array $seed): void {
            // Create Status
            if(isset($seed['action']) && $seed['action'] === 'create') {
                // Create Campaign
                $campaign = factory(Campaign::class)->create([
                    'user_id' => $this->dealer->getKey(),
                    'campaign_name' => $seed['name'],
                    'campaign_subject' => $seed['subject'] ?? $seed['name']
                ]);

                $this->createdCampaigns[] = $campaign;
                return;
            }

            // Make Campaign
            $campaign = factory(Campaign::class)->make([
                'user_id' => $this->dealer->getKey(),
                'campaign_name' => $seed['name'],
                'campaign_subject' => $seed['subject'] ?? $seed['name']
            ]);

            $this->missingCampaigns[] = $campaign;
        });


        // Create Sent Entries
        $sentSeeds = [
            ['action' => 'create'],
            ['action' => 'create'],
            ['action' => 'create'],
            [],
            [],
            []
        ];

        collect($sentSeeds)->each(function (array $seed): void {
            // Create Lead
            $lead = factory(Lead::class)->create([
                'dealer_id' => $this->dealer->getKey()
            ]);
            $this->leads[] = $lead;

            // Create Campaign Sent
            if(isset($seed['action']) && $seed['action'] === 'create') {
                // Create Campaign Sent
                $sent = factory(CampaignSent::class)->create([
                    'email_campaigns_id' => $this->createdCampaigns[0]->getKey(),
                    'lead_id' => $lead->getKey()
                ]);

                $this->campaignsSent[] = $sent;
                return;
            }

            // Make Campaign Sent
            $sent = factory(CampaignSent::class)->make([
                'email_campaigns_id' => $this->createdCampaigns[0]->getKey(),
                'lead_id' => $lead->getKey()
            ]);

            $this->campaignsUnsent[] = $sent;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        // Database clean up
        if(!empty($this->createdCampaigns) && count($this->createdCampaigns)) {
            foreach($this->createdCampaigns as $campaign) {
                $campaignId = $campaign->email_campaigns_id;
                CampaignSent::where('email_campaigns_id', $campaignId)->delete();
                Campaign::destroy($campaignId);
            }
        }
        Template::where('user_id', $dealerId)->delete();
        Lead::where('dealer_id', $dealerId)->delete();
        SalesPerson::where('user_id', $dealerId)->delete();
        NewUser::destroy($dealerId);
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();                
        User::destroy($dealerId);
    }
}