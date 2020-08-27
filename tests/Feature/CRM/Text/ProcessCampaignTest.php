<?php

namespace Tests\Feature\CRM\Text;

use App\Exceptions\CRM\Text\NoLeadsTestDeliverCampaignException;
use App\Services\CRM\Text\TextServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Text\Campaign;
use App\Models\CRM\Text\Template;
use App\Models\User\NewDealerUser;
use Faker\Factory as Faker;
use Tests\TestCase;

class ProcessCampaignTest extends TestCase
{
    /**
     * App\Repositories\CRM\Text\CampaignRepositoryInterface $campaigns
     * App\Repositories\CRM\Text\TemplateRepositoryInterface $templates
     * App\Repositories\User\DealerLocationRepository $dealerLocation
     */
    protected $campaigns;
    protected $templates;
    protected $dealerLocation;

    /**
     * Faker\Generator $faker
     */
    protected $faker;

    /**
     * Set Up Test
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make Lead Repo
        $this->campaigns = $this->app->make('App\Repositories\CRM\Text\CampaignRepositoryInterface');
        $this->templates = $this->app->make('App\Repositories\CRM\Text\TemplateRepositoryInterface');
        $this->dealerLocation = $this->app->make('App\Repositories\User\DealerLocationRepositoryInterface');

        // Create Faker
        $this->faker = Faker::create();
    }

    /**
     * Test simple campaign
     * 
     * @specs string action = inquired
     * @specs array location_id = null
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testSimpleCampaign()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Refresh Leads
        $this->refreshCampaigns($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create();
        }

        // Build Generic Campaign
        $campaign = factory(Campaign::class)->create();
        $unused = $this->refreshLeads($campaign->id);

        // Get Campaigns for Dealer
        $campaigns = $this->campaigns->getAll([
            'is_enabled' => true,
            'per_page' => 'all',
            'user_id' => $dealer->user_id
        ]);
        foreach($campaigns as $single) {
            $campaign = $single;
            break;
        }
        $leads = $campaign->leads;
        if(count($leads) < 1) {
            throw new NoLeadsTestDeliverCampaignException();
        }

        // Mock Text Service
        $this->mock(TextServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $campaign) {
            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {
                // Get From Number
                $from_number = $campaign->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory->title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->once();
            }

            // Loop Leads to Mock Text NOT Sent
            foreach($unused as $lead) {
                // Get From Number
                $from_number = $campaign->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory->title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->never();
            }
        });

        // Call Leads Assign Command
        $this->artisan('text:process-campaign ' . self::getTestDealerId())->assertExitCode(0);


        // Loop Leads
        foreach($leads as $lead) {
            // Get From Number
            $from_number = $campaign->from_sms_number;
            if(empty($from_number)) {
                $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
            }

            // Get Text Message
            $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                'lead_name' => $lead->full_name,
                'title_of_unit_of_interest' => $lead->inventory->title,
                'dealer_name' => $dealer->user->name
            ]);

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('dealer_texts_log', [
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_phone
            ]);

            // Assert a text campaign was logged sent
            $this->assertDatabaseHas('crm_text_campaign_sent', [
                'text_campaign_id' => $campaign->id,
                'lead_id' => $lead->identifier,
                'status' => 'logged'
            ]);
        }
    }


    /**
     * Refresh Campaigns in DB
     * 
     * @param int $userId
     * @return void
     */
    private function refreshCampaigns($userId) {
        // Cancel All Dealer Campaigns
        Campaign::where('user_id', $userId)->update([
            'is_enabled' => false
        ]);
    }

    /**
     * Refresh Campaign Leads in DB
     * 
     * @param int $campaignId
     * @return array of leads outside of range
     */
    private function refreshLeads($campaignId) {
        // Get Existing Unassigned Leads for Dealer ID
        $campaign = Campaign::find($campaignId);

        // Loop Leads
        if(count($campaign->leads) > 0) {
            foreach($campaign->leads as $lead) {
                Lead::where('identifier', $lead->identifier)->delete();
            }
        }

        // Create 10 Leads From X Days Ago to X+10 Days Ago
        for($n = 0; $n < 10; $n++) {
            // Get Random Date Since "Send After Days"
            factory(Lead::class)->create([
                'date_submitted' => $this->faker->dateTimeBetween('-' . ($campaign->send_after_days + 10) . ' days', '-' . $campaign->send_after_days . ' days')
            ]);
        }

        // Create 5 Leads Within X Days
        $leads = array();
        for($n = 0; $n < 5; $n++) {
            // Get Random Date Since "Send After Days"
            $leads[] = factory(Lead::class)->create([
                'date_submitted' => $this->faker->dateTimeBetween('-' . $campaign->send_after_days . ' days')
            ]);
        }

        // Create 5 Leads In Last X+10 Days to X+25 Days
        for($n = 0; $n < 5; $n++) {
            // Get Random Date Since "Send After Days"
            $leads[] = factory(Lead::class)->create([
                'date_submitted' => $this->faker->dateTimeBetween('-' . ($campaign->send_after_days + 25) . ' days', '-' . ($campaign->send_after_days + 10) . ' days')
            ]);
        }
        return $leads;
    }
}
