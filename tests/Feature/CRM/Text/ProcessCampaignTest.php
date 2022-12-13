<?php

namespace Tests\Feature\CRM\Text;

use App\Exceptions\CRM\Text\NoLeadsProcessCampaignException;
use App\Services\CRM\Text\TwilioServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Category;
use App\Models\Inventory\Manufacturers\Manufacturers;
use App\Models\CRM\Text\Campaign;
use App\Models\CRM\Text\CampaignBrand;
use App\Models\CRM\Text\CampaignCategory;
use App\Models\CRM\Text\Template;
use App\Models\User\NewDealerUser;
use Faker\Factory as Faker;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use App\Models\Observers\CRM\Interactions\TextLogObserver;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Models\User\NewUser;
use App\Models\User\User;
use App\Models\User\CrmUser;
use App\Models\Website\Website;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Models\User\DealerLocation;

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
     * App\Models\User\User $dealer
     */
    protected $dealer;

    /**
     * @const int
     */
    const ENTITY_TYPE_ID = 1;

    /**
     * 
     */
    const FROM_SMS_NUMBER = 'FROM_SMS_NUMBER';

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

        // create new dealer
        $this->dealer = factory(User::class)->create();

        /**
         * necessary data for dealer
         */
        $user = factory(NewUser::class)->create();
        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);
        $newDealerUser = $newDealerUserRepo->create([
            'user_id' => $user->user_id,
            'salt' => md5((string)$user->user_id), // random string
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ]);
        $this->dealer->newDealerUser()->save($newDealerUser);
        $crmUserRepo = app(CrmUserRepositoryInterface::class);
        $crmUserRepo->create([
            'user_id' => $user->user_id,
            'logo' => '',
            'first_name' => '',
            'last_name' => '',
            'display_name' => '',
            'dealer_name' => $this->dealer->name,
            'active' => 1
        ]);
        // END

        factory(DealerLocation::class, 3)->create([
            'dealer_id' => $this->dealer->getKey()
        ]);

        $website = factory(Website::class)->create();
        $this->dealer->website()->save($website);
    }

    public function tearDown(): void
    {
        Campaign::where('user_id', $this->dealer->newDealerUser->user_id)->each(function($campaign) {
            $campaign->categories()->delete();
            $campaign->brands()->delete();
            $campaign->sent()->delete();
        });
        Campaign::where('user_id', $this->dealer->newDealerUser->user_id)->delete();
        Template::where('user_id', $this->dealer->newDealerUser->user_id)->delete();

        Lead::where('dealer_id', $this->dealer->getKey())->each(function($lead) {
            $lead->textLogs()->delete();
        });
        Lead::where('dealer_id', $this->dealer->getKey())->delete();
        Inventory::where('dealer_id', $this->dealer->getKey());

        DealerLocation::where('dealer_id', $this->dealer->getKey())->delete();
        Website::where('dealer_id', $this->dealer->getKey())->delete();

        CrmUser::destroy($this->dealer->getKey());
        NewDealerUser::destroy($this->dealer->getKey());
        NewUser::destroy($this->dealer->getKey());
        User::destroy($this->dealer->getKey());

        parent::tearDown();
    }

    /**
     * Test simple campaign
     *
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = null
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testSimpleCampaign()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail($this->dealer->getKey());

        // Refresh Leads
        $this->refreshCampaigns($dealer->user_id);

        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create([
                'user_id' => $dealer->user_id
            ]);
        }

        // Build Generic Campaign
        $campaign = factory(Campaign::class)->create([
            'user_id' => $dealer->user_id
        ]);
        $unused = $this->refreshLeads($campaign->id);

        // Get Campaigns for Dealer
        $campaigns = $this->campaigns->getAllActive($dealer->user_id);
        foreach($campaigns as $single) {
            $campaign = $single;
            break;
        }
        $leads = $campaign->leads;
        if(count($leads) < 1) {
            throw new NoLeadsProcessCampaignException();
        }

        // skip elasticsearch for now
        $this->mock(TextLogObserver::class, function ($mock) use ($leads) {

            $mock->shouldReceive('created')
                ->times(count($leads));
        });

        // Mock CampaignService@getFromNumber
        $from_number = $campaign->from_sms_number ?? self::FROM_SMS_NUMBER;

        $this->mock(DealerLocationRepositoryInterface::class, function ($mock) use ($from_number, $dealer, $campaign, $leads) {

            $mock->shouldReceive('findDealerSmsNumber')
                ->withArgs([$dealer->id])
                ->andReturn($from_number);
        });

        // Mock Text Service
        $this->mock(TwilioServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $campaign, $from_number) {

            $mock->shouldReceive('isValidPhoneNumber')
                ->with($from_number)
                ->andReturn(true);

            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->once();
            }

            // Loop Leads to Mock Text NOT Sent
            foreach($unused as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->never();
            }
        });

        // Call Leads Assign Command
        $this->artisan('text:process-campaign ' . $this->dealer->getKey())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {

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
     * Test campaign on purchases
     *
     * @group CRM
     * @specs string action = purchased
     * @specs array location_id = null
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testPurchasesCampaign()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail($this->dealer->getKey());

        // Refresh Leads
        $this->refreshCampaigns($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create([
                'user_id' => $dealer->user_id
            ]);
        }

        // Build Generic Campaign
        $campaign = factory(Campaign::class)->create([
            'action' => 'purchased',
            'user_id' => $dealer->user_id
        ]);
        $unused = $this->refreshLeads($campaign->id, [
            'action' => 'purchased'
        ]);

        // Get Campaigns for Dealer
        $campaigns = $this->campaigns->getAllActive($dealer->user_id);
        foreach($campaigns as $single) {
            $campaign = $single;
            break;
        }
        $leads = $campaign->leads;
        if(count($leads) < 1) {
            throw new NoLeadsProcessCampaignException();
        }

        // skip elasticsearch for now
        $this->mock(TextLogObserver::class, function ($mock) use ($leads) {

            $mock->shouldReceive('created')
                ->times(count($leads));
        });

        // Mock CampaignService@getFromNumber
        $from_number = $campaign->from_sms_number ?? self::FROM_SMS_NUMBER;

        $this->mock(DealerLocationRepositoryInterface::class, function ($mock) use ($from_number, $dealer, $campaign, $leads) {

            $mock->shouldReceive('findDealerSmsNumber')
                ->withArgs([$dealer->id])
                ->andReturn($from_number);
        });

        // Mock Text Service
        $this->mock(TwilioServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $campaign, $from_number) {

            $mock->shouldReceive('isValidPhoneNumber')
                ->with($from_number)
                ->andReturn(true);

            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->once();
            }

            // Loop Leads to Mock Text NOT Sent
            foreach($unused as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->never();
            }
        });

        // Call Leads Assign Command
        $this->artisan('text:process-campaign ' . $this->dealer->getKey())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {

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
     * Test campaign by location
     *
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = first
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testLocationSpecificCampaign()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail($this->dealer->getKey());

        // Refresh Leads
        $this->refreshCampaigns($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create([
                'user_id' => $dealer->user_id
            ]);
        }

        // Build Random Factory Salespeople
        $locationIds = DealerLocation::where('dealer_id', $this->dealer->getKey())->get()->pluck('id')->toArray();
        $locationId = reset($locationIds);
        $lastLocationId = end($locationIds);

        // Build Generic Campaign
        $campaign = factory(Campaign::class)->create([
            'location_id' => $locationId,
            'user_id' => $dealer->user_id
        ]);
        $unused = $this->refreshLeads($campaign->id, [
            'location_id' => $locationId,
            'unused_location_id' => $lastLocationId
        ]);

        // Get Campaigns for Dealer
        $campaigns = $this->campaigns->getAllActive($dealer->user_id);
        foreach($campaigns as $single) {
            $campaign = $single;
            break;
        }
        $leads = $campaign->leads;
        if(count($leads) < 1) {
            throw new NoLeadsProcessCampaignException();
        }

        // skip elasticsearch for now
        $this->mock(TextLogObserver::class, function ($mock) use ($leads) {

            $mock->shouldReceive('created')
                ->times(count($leads));
        });

        // Mock CampaignService@getFromNumber
        $from_number = $campaign->from_sms_number ?? self::FROM_SMS_NUMBER;

        $this->mock(DealerLocationRepositoryInterface::class, function ($mock) use ($from_number, $dealer, $campaign, $leads) {

            $mock->shouldReceive('findDealerSmsNumber')
                ->withArgs([$dealer->id])
                ->andReturn($from_number);
        });

        // Mock Text Service
        $this->mock(TwilioServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $campaign, $from_number) {

            $mock->shouldReceive('isValidPhoneNumber')
                ->with($from_number)
                ->andReturn(true);

            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->once();
            }

            // Loop Leads to Mock Text NOT Sent
            foreach($unused as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->never();
            }
        });

        // Call Leads Assign Command
        $this->artisan('text:process-campaign ' . $this->dealer->getKey())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {

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
     * Test campaign for archived leads
     *
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 1
     * @return void
     */
    public function testArchivedCampaign()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail($this->dealer->getKey());

        // Refresh Leads
        $this->refreshCampaigns($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create([
                'user_id' => $dealer->user_id
            ]);
        }

        // Build Generic Campaign
        $campaign = factory(Campaign::class)->create([
            'include_archived' => 1,
            'user_id' => $dealer->user_id
        ]);
        $unused = $this->refreshLeads($campaign->id, [
            'is_archived' => 1
        ]);

        // Get Campaigns for Dealer
        $campaigns = $this->campaigns->getAllActive($dealer->user_id);
        foreach($campaigns as $single) {
            $campaign = $single;
            break;
        }
        $leads = $campaign->leads;
        if(count($leads) < 1) {
            throw new NoLeadsProcessCampaignException();
        }

        // skip elasticsearch for now
        $this->mock(TextLogObserver::class, function ($mock) use ($leads) {

            $mock->shouldReceive('created')
                ->times(count($leads));
        });

        // Mock CampaignService@getFromNumber
        $from_number = $campaign->from_sms_number ?? self::FROM_SMS_NUMBER;

        $this->mock(DealerLocationRepositoryInterface::class, function ($mock) use ($from_number, $dealer, $campaign, $leads) {

            $mock->shouldReceive('findDealerSmsNumber')
                ->withArgs([$dealer->id])
                ->andReturn($from_number);
        });

        // Mock Text Service
        $this->mock(TwilioServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $campaign, $from_number) {

            $mock->shouldReceive('isValidPhoneNumber')
                ->with($from_number)
                ->andReturn(true);

            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->once();
            }

            // Loop Leads to Mock Text NOT Sent
            foreach($unused as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->never();
            }
        });

        // Call Leads Assign Command
        $this->artisan('text:process-campaign ' . $this->dealer->getKey())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {

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
     * Test campaign with brands
     *
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testBrandCampaign()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail($this->dealer->getKey());

        // Refresh Leads
        $this->refreshCampaigns($dealer->user_id);

        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create([
                'user_id' => $dealer->user_id
            ]);
        }

        // Get Random Brands
        $brands = Manufacturers::inRandomOrder()->take(3)->pluck('name')->toArray();
        $unusedBrands = Manufacturers::whereNotIn('name', $brands)->inRandomOrder()->take(3)->pluck('name')->toArray();

        // Build Generic Campaign
        $campaign = factory(Campaign::class)->create([
            'user_id' => $dealer->user_id
        ]);
        $campaign->each(function ($campaign) use($brands) {
            // Add Campaign Brands
            foreach($brands as $brand) {
                $campaign->brands()->save(factory(CampaignBrand::class)->make([
                    'brand' => $brand
                ]));
            }
        });
        $unused = $this->refreshLeads($campaign->id, [
            'brands' => $brands,
            'unused_brands' => $unusedBrands
        ]);

        // Get Campaigns for Dealer
        $campaigns = $this->campaigns->getAllActive($dealer->user_id);
        foreach($campaigns as $single) {
            $campaign = $single;
            break;
        }
        $leads = $campaign->leads;
        if(count($leads) < 1) {
            throw new NoLeadsProcessCampaignException();
        }

        // skip elasticsearch for now
        $this->mock(TextLogObserver::class, function ($mock) use ($leads) {

            $mock->shouldReceive('created')
                ->times(count($leads));
        });

        // Mock CampaignService@getFromNumber
        $from_number = $campaign->from_sms_number ?? self::FROM_SMS_NUMBER;

        $this->mock(DealerLocationRepositoryInterface::class, function ($mock) use ($from_number, $dealer, $campaign, $leads) {

            $mock->shouldReceive('findDealerSmsNumber')
                ->withArgs([$dealer->id])
                ->andReturn($from_number);
        });

        // Mock Text Service
        $this->mock(TwilioServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $campaign, $from_number) {

            $mock->shouldReceive('isValidPhoneNumber')
                ->with($from_number)
                ->andReturn(true);

            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->once();
            }

            // Loop Leads to Mock Text NOT Sent
            foreach($unused as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->never();
            }
        });

        // Call Leads Assign Command
        $this->artisan('text:process-campaign ' . $this->dealer->getKey())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {

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
     * Test campaign with categories
     *
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testCategoryCampaign()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail($this->dealer->getKey());

        // Refresh Leads
        $this->refreshCampaigns($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create([
                'user_id' => $dealer->user_id
            ]);
        }

        // Get Random Categories
        $categories = Category::where('entity_type_id', self::ENTITY_TYPE_ID)->inRandomOrder()->take(3)->pluck('legacy_category')->toArray();
        $unusedCategories = Category::where('entity_type_id', self::ENTITY_TYPE_ID)->whereNotIn('legacy_category', $categories)->inRandomOrder()->take(3)->pluck('legacy_category')->toArray();

        // Build Generic Campaign
        $campaign = factory(Campaign::class)->create([
            'user_id' => $dealer->user_id
        ]);
        $campaign->each(function ($campaign) use($categories) {
            // Add Campaign Categories
            foreach($categories as $cat) {
                $campaign->categories()->save(factory(CampaignCategory::class)->make([
                    'category' => $cat
                ]));
            }
        });
        $unused = $this->refreshLeads($campaign->id, [
            'entity_type_id' => self::ENTITY_TYPE_ID,
            'categories' => $categories,
            'unused_categories' => $unusedCategories
        ]);

        // Get Campaigns for Dealer
        $campaigns = $this->campaigns->getAllActive($dealer->user_id);
        foreach($campaigns as $single) {
            $campaign = $single;
            break;
        }
        $leads = $campaign->leads;
        if(count($leads) < 1) {
            throw new NoLeadsProcessCampaignException();
        }

        // Mock CampaignService@getFromNumber
        $from_number = $campaign->from_sms_number ?? self::FROM_SMS_NUMBER;

        $this->mock(DealerLocationRepositoryInterface::class, function ($mock) use ($from_number, $dealer, $campaign, $leads) {

            $mock->shouldReceive('findDealerSmsNumber')
                ->withArgs([$dealer->id])
                ->andReturn($from_number);
        });

        // skip elasticsearch for now
        $this->mock(TextLogObserver::class, function ($mock) use ($leads) {

            $mock->shouldReceive('created')
                ->times(count($leads));
        });

        // Mock Text Service
        $this->mock(TwilioServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $campaign, $from_number) {
                
            $mock->shouldReceive('isValidPhoneNumber')
                ->with($from_number)
                ->andReturn(true);

            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->once();
            }

            // Loop Leads to Mock Text NOT Sent
            foreach($unused as $lead) {

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($campaign->template->template, [
                    'lead_name' => $lead->full_name,
                    'title_of_unit_of_interest' => $lead->inventory_title,
                    'dealer_name' => $dealer->user->name
                ]);

                // Should Receive Send With Args Once!
                $mock->shouldReceive('send')
                     ->withArgs([$from_number, $lead->text_phone, $textMessage, $lead->full_name])
                     ->never();
            }
        });

        // Call Leads Assign Command
        $this->artisan('text:process-campaign ' . $this->dealer->getKey())->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {

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
     * @param array $filters
     * @return array of leads outside of range
     */
    private function refreshLeads($campaignId, $filters = []) {
        // Get Existing Unassigned Leads for Dealer ID
        $campaign = Campaign::find($campaignId);

        // Get Website ID
        $dealer = NewDealerUser::findOrFail($this->dealer->getKey());
        $websiteId = $dealer->website->id;

        // Loop Leads
        if(count($campaign->leads) > 0) {
            foreach($campaign->leads as $lead) {
                Lead::where('identifier', $lead->identifier)->delete();
            }
        }

        // Create 10 Leads That Match the Campaign!
        for($n = 0; $n < 10; $n++) {
            // Get Random Date Since "Send After Days"
            $params = [
                'website_id' => $websiteId,
                'dealer_id' => $dealer->id,
                'date_submitted' => $this->faker->dateTimeBetween('-' . ($campaign->send_after_days + 10) . ' days', '-' . $campaign->send_after_days . ' days')
            ];

            // Insert With Manufacturer or Category
            if(isset($filters['brands']) || isset($filters['categories'])) {
                // Initialize Lead Params
                $leadParams = ['dealer_id' => $dealer->getKey()];

                // Insert With Manufacturer
                if(isset($filters['brands'])) {
                    // Pick a Random (Valid) Brand
                    $brandKey = array_rand($filters['brands']);
                    $brand = $filters['brands'][$brandKey];

                    // Add MFG
                    $leadParams['manufacturer'] = $brand;
                }

                // Insert With Manufacturer
                if(isset($filters['categories'])) {
                    // Pick a Random (Valid) Category
                    $catKey = array_rand($filters['categories']);
                    $cat = $filters['categories'][$catKey];

                    // Set Params
                    $leadParams['entity_type_id'] = $filters['entity_type_id'] ?: 1;
                    $leadParams['category'] = $cat;
                }

                // Add Inventory to Lead
                $inventory = factory(Inventory::class)->create($leadParams);

                // Add Inventory ID
                $params['inventory_id'] = $inventory->inventory_id;
            }

            // Insert With Location ID
            if(isset($filters['location_id'])) {
                $params['dealer_location_id'] = $filters['location_id'];
            }

            // Insert With Archived Status
            if(isset($filters['is_archived'])) {
                $params['is_archived'] = $filters['is_archived'];
            }

            // Insert Leads Into DB
            $lead = factory(Lead::class)->create($params);

            // Add Done Status
            if(isset($filters['action']) && $filters['action'] === 'purchased') {
                factory(LeadStatus::class)->create([
                    'tc_lead_identifier' => $lead->identifier,
                    'status' => Lead::STATUS_WON_CLOSED
                ]);
            }
        }

        // Create Leads That DON'T Match the Criteria!
        $leads = array();
        for($n = 0; $n < 5; $n++) {
            // Initialize Empty Params
            $params = [
                'website_id' => $websiteId,
                'dealer_id' => $dealer->id,
            ];

            // Insert With Manufacturer or Category
            if(isset($filters['unused_brands']) || isset($filters['unused_categories'])) {
                // Initialize Lead Params
                $leadParams = ['dealer_id' => $dealer->getKey()];

                // Insert With Manufacturer
                if(isset($filters['unused_brands'])) {
                    // Pick a Random (Valid) Brand
                    $brandKey = array_rand($filters['unused_brands']);
                    $brand = $filters['unused_brands'][$brandKey];

                    // Add MFG
                    $leadParams['manufacturer'] = $brand;
                }

                // Insert With Manufacturer
                if(isset($filters['unused_categories'])) {
                    // Pick a Random (Valid) Category
                    $catKey = array_rand($filters['unused_categories']);
                    $cat = $filters['unused_categories'][$catKey];

                    // Set Params
                    $leadParams['entity_type_id'] = $filters['entity_type_id'] ?: 1;
                    $leadParams['category'] = $cat;
                }

                // Add Inventory to Lead
                $inventory = factory(Inventory::class)->create($leadParams);

                // Add Inventory ID
                $params['inventory_id'] = $inventory->inventory_id;
            }

            // Insert With Location ID
            if(isset($filters['unused_location_id'])) {
                $params['dealer_location_id'] = $filters['unused_location_id'];
            }

            // Insert With Archived Status
            if(isset($filters['is_archived'])) {
                $params['is_archived'] = !empty($filters['is_archived']) ? 0 : 1;
            }

            // No Other Params Set?! Make Date Not Match Criteria!
            if(empty($params)) {
                $params['date_submitted'] = $this->faker->dateTimeBetween('-' . $campaign->send_after_days . ' days');
            } else {
                $params['date_submitted'] = $this->faker->dateTimeBetween('-' . ($campaign->send_after_days + 10) . ' days', '-' . $campaign->send_after_days . ' days');
            }

            // Insert Leads Into DB
            $leads[] = factory(Lead::class)->create($params);
        }

        // Create 5 Leads In Last X+10 Days to X+25 Days
        for($n = 0; $n < 5; $n++) {
            // Initialize Empty Params
            $params = [
                'website_id' => $websiteId,
                'dealer_id' => $dealer->id,
            ];

            // Insert With Manufacturer or Category
            if(isset($filters['unused_brands']) || isset($filters['unused_categories'])) {
                // Initialize Lead Params
                $leadParams = ['dealer_id' => $dealer->getKey()];

                // Insert With Manufacturer
                if(isset($filters['unused_brands'])) {
                    // Pick a Random (Valid) Brand
                    $brandKey = array_rand($filters['unused_brands']);
                    $brand = $filters['unused_brands'][$brandKey];

                    // Add MFG
                    $leadParams['manufacturer'] = $brand;
                }

                // Insert With Manufacturer
                if(isset($filters['unused_categories'])) {
                    // Pick a Random (Valid) Category
                    $catKey = array_rand($filters['unused_categories']);
                    $cat = $filters['unused_categories'][$catKey];

                    // Set Params
                    $leadParams['entity_type_id'] = $filters['entity_type_id'] ?: 1;
                    $leadParams['category'] = $cat;
                }

                // Add Inventory to Lead
                $inventory = factory(Inventory::class)->create($leadParams);

                // Add Inventory ID
                $params['inventory_id'] = $inventory->inventory_id;
            }

            // Insert With Location ID
            if(isset($filters['unused_location_id'])) {
                $params['dealer_location_id'] = $filters['unused_location_id'];
            }

            // Insert With Archived Status
            if(isset($filters['is_archived'])) {
                $params['is_archived'] = !empty($filters['is_archived']) ? 0 : 1;
            }

            // No Other Params Set?! Make Date Not Match Criteria!
            if(empty($params)) {
                $params['date_submitted'] = $this->faker->dateTimeBetween('-' . ($campaign->send_after_days + 25) . ' days', '-' . ($campaign->send_after_days + 10) . ' days');
            } else {
                $params['date_submitted'] = $this->faker->dateTimeBetween('-' . ($campaign->send_after_days + 10) . ' days', '-' . $campaign->send_after_days . ' days');
            }

            // Insert Leads Into DB
            $leads[] = factory(Lead::class)->create($params);
        }
        return $leads;
    }
}
