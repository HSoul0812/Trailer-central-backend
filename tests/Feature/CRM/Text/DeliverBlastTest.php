<?php

namespace Tests\Feature\CRM\Text;

use App\Exceptions\CRM\Text\NoLeadsDeliverBlastException;
use App\Services\CRM\Text\TextServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Category;
use App\Models\Inventory\Manufacturers\Manufacturers;
use App\Models\CRM\Text\Blast;
use App\Models\CRM\Text\BlastBrand;
use App\Models\CRM\Text\BlastCategory;
use App\Models\CRM\Text\Template;
use App\Models\User\NewDealerUser;
use Faker\Factory as Faker;
use Tests\TestCase;

class DeliverBlastTest extends TestCase
{
    /**
     * App\Repositories\CRM\Text\BlastRepositoryInterface $blasts
     * App\Repositories\CRM\Text\TemplateRepositoryInterface $templates
     * App\Repositories\User\DealerLocationRepository $dealerLocation
     */
    protected $blasts;
    protected $templates;
    protected $dealerLocation;

    /**
     * Faker\Generator $faker
     */
    protected $faker;

    /**
     * @const int
     */
    const ENTITY_TYPE_ID = 1;

    /**
     * Set Up Test
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make Lead Repo
        $this->blasts = $this->app->make('App\Repositories\CRM\Text\BlastRepositoryInterface');
        $this->templates = $this->app->make('App\Repositories\CRM\Text\TemplateRepositoryInterface');
        $this->dealerLocation = $this->app->make('App\Repositories\User\DealerLocationRepositoryInterface');

        // Create Faker
        $this->faker = Faker::create();
    }

    /**
     * Test simple blast
     * 
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = null
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testSimpleBlast()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Refresh Leads
        $this->refreshBlasts($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create();
        }

        // Build Generic Blast
        $blast = factory(Blast::class)->create();
        $unused = $this->refreshLeads($blast->id);

        // Get Blasts for Dealer
        $blasts = $this->blasts->getAllActive($dealer->user_id);
        foreach($blasts as $single) {
            $blast = $single;
            break;
        }
        $leads = $blast->leads;
        if(count($leads) < 1) {
            throw new NoLeadsDeliverBlastException();
        }

        // Mock Text Service
        $this->mock(TextServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $blast) {
            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {
                // Get From Number
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
        $this->artisan('text:deliver-blast ' . self::getTestDealerId())->assertExitCode(0);


        // Loop Leads
        foreach($leads as $lead) {
            // Get From Number
            $from_number = $blast->from_sms_number;
            if(empty($from_number)) {
                $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
            }

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('dealer_texts_log', [
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_phone
            ]);

            // Assert a text blast was logged sent
            $this->assertDatabaseHas('crm_text_blast_sent', [
                'text_blast_id' => $blast->id,
                'lead_id' => $lead->identifier,
                'status' => 'logged'
            ]);
        }
    }

    /**
     * Test blast on purchases
     * 
     * @group CRM
     * @specs string action = purchased
     * @specs array location_id = null
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testPurchasesBlast()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Refresh Leads
        $this->refreshBlasts($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create();
        }

        // Build Generic Blast
        $blast = factory(Blast::class)->create([
            'action' => 'purchased'
        ]);
        $unused = $this->refreshLeads($blast->id, [
            'action' => 'purchased'
        ]);

        // Get Blasts for Dealer
        $blasts = $this->blasts->getAllActive($dealer->user_id);
        foreach($blasts as $single) {
            $blast = $single;
            break;
        }
        $leads = $blast->leads;
        if(count($leads) < 1) {
            throw new NoLeadsDeliverBlastException();
        }

        // Mock Text Service
        $this->mock(TextServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $blast) {
            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {
                // Get From Number
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
        $this->artisan('text:deliver-blast ' . self::getTestDealerId())->assertExitCode(0);


        // Loop Leads
        foreach($leads as $lead) {
            // Get From Number
            $from_number = $blast->from_sms_number;
            if(empty($from_number)) {
                $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
            }

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('dealer_texts_log', [
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_phone
            ]);

            // Assert a text blast was logged sent
            $this->assertDatabaseHas('crm_text_blast_sent', [
                'text_blast_id' => $blast->id,
                'lead_id' => $lead->identifier,
                'status' => 'logged'
            ]);
        }
    }

    /**
     * Test blast by location
     * 
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = first
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testLocationSpecificBlast()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Refresh Leads
        $this->refreshBlasts($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create();
        }

        // Build Random Factory Salespeople
        $locationIds = TestCase::getTestDealerLocationIds();
        $locationId = reset($locationIds);
        $lastLocationId = end($locationIds);

        // Build Generic Blast
        $blast = factory(Blast::class)->create([
            'location_id' => $locationId
        ]);
        $unused = $this->refreshLeads($blast->id, [
            'location_id' => $locationId,
            'unused_location_id' => $lastLocationId
        ]);

        // Get Blasts for Dealer
        $blasts = $this->blasts->getAllActive($dealer->user_id);
        foreach($blasts as $single) {
            $blast = $single;
            break;
        }
        $leads = $blast->leads;
        if(count($leads) < 1) {
            throw new NoLeadsDeliverBlastException();
        }

        // Mock Text Service
        $this->mock(TextServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $blast) {
            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {
                // Get From Number
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
        $this->artisan('text:deliver-blast ' . self::getTestDealerId())->assertExitCode(0);


        // Loop Leads
        foreach($leads as $lead) {
            // Get From Number
            $from_number = $blast->from_sms_number;
            if(empty($from_number)) {
                $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
            }

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('dealer_texts_log', [
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_phone
            ]);

            // Assert a text blast was logged sent
            $this->assertDatabaseHas('crm_text_blast_sent', [
                'text_blast_id' => $blast->id,
                'lead_id' => $lead->identifier,
                'status' => 'logged'
            ]);
        }
    }

    /**
     * Test blast by archived status
     * 
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 1
     * @return void
     */
    public function testArchivedBlast()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Refresh Leads
        $this->refreshBlasts($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create();
        }

        // Build Generic Blast
        $blast = factory(Blast::class)->create([
            'include_archived' => 1
        ]);
        $unused = $this->refreshLeads($blast->id, [
            'is_archived' => 1
        ]);

        // Get Blasts for Dealer
        $blasts = $this->blasts->getAllActive($dealer->user_id);
        foreach($blasts as $single) {
            $blast = $single;
            break;
        }
        $leads = $blast->leads;
        if(count($leads) < 1) {
            throw new NoLeadsDeliverBlastException();
        }

        // Mock Text Service
        $this->mock(TextServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $blast) {
            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {
                // Get From Number
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
        $this->artisan('text:deliver-blast ' . self::getTestDealerId())->assertExitCode(0);


        // Loop Leads
        foreach($leads as $lead) {
            // Get From Number
            $from_number = $blast->from_sms_number;
            if(empty($from_number)) {
                $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
            }

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('dealer_texts_log', [
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_phone
            ]);

            // Assert a text blast was logged sent
            $this->assertDatabaseHas('crm_text_blast_sent', [
                'text_blast_id' => $blast->id,
                'lead_id' => $lead->identifier,
                'status' => 'logged'
            ]);
        }
    }

    /**
     * Test blast with brands
     * 
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testBrandBlast()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Refresh Leads
        $this->refreshBlasts($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create();
        }

        // Get Random Brands
        $brands = Manufacturers::inRandomOrder()->take(3)->pluck('name')->toArray();
        $unusedBrands = Manufacturers::whereNotIn('name', $brands)->inRandomOrder()->take(3)->pluck('name')->toArray();

        // Build Generic Blast
        $blast = factory(Blast::class)->create();
        $blast->each(function ($blast) use($brands) {
            // Add Campaign Brands
            foreach($brands as $brand) {
                $blast->brands()->save(factory(BlastBrand::class)->make([
                    'brand' => $brand
                ]));
            }
        });
        $unused = $this->refreshLeads($blast->id, [
            'brands' => $brands,
            'unused_brands' => $unusedBrands
        ]);

        // Get Blasts for Dealer
        $blasts = $this->blasts->getAllActive($dealer->user_id);
        foreach($blasts as $single) {
            $blast = $single;
            break;
        }
        $leads = $blast->leads;
        if(count($leads) < 1) {
            throw new NoLeadsDeliverBlastException();
        }

        // Mock Text Service
        $this->mock(TextServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $blast) {
            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {
                // Get From Number
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
        $this->artisan('text:deliver-blast ' . self::getTestDealerId())->assertExitCode(0);


        // Loop Leads
        foreach($leads as $lead) {
            // Get From Number
            $from_number = $blast->from_sms_number;
            if(empty($from_number)) {
                $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
            }

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('dealer_texts_log', [
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_phone
            ]);

            // Assert a text blast was logged sent
            $this->assertDatabaseHas('crm_text_blast_sent', [
                'text_blast_id' => $blast->id,
                'lead_id' => $lead->identifier,
                'status' => 'logged'
            ]);
        }
    }

    /**
     * Test blast with categories
     * 
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testCategoryBlast()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Refresh Leads
        $this->refreshBlasts($dealer->user_id);


        // Build Generic Template
        $template = Template::where('user_id', $dealer->user_id)->first();
        if(empty($template->id)) {
            factory(Template::class)->create();
        }

        // Get Random Categories
        $categories = Category::where('entity_type_id', self::ENTITY_TYPE_ID)->inRandomOrder()->take(3)->pluck('legacy_category')->toArray();
        $unusedCategories = Category::where('entity_type_id', self::ENTITY_TYPE_ID)->whereNotIn('legacy_category', $categories)->inRandomOrder()->take(3)->pluck('legacy_category')->toArray();

        // Build Generic Blast
        $blast = factory(Blast::class)->create();
        $blast->each(function ($blast) use($categories) {
            // Add Campaign Categories
            foreach($categories as $cat) {
                $blast->brands()->save(factory(BlastCategory::class)->make([
                    'category' => $cat
                ]));
            }
        });
        $unused = $this->refreshLeads($blast->id, [
            'entity_type_id' => self::ENTITY_TYPE_ID,
            'categories' => $categories,
            'unused_categories' => $unusedCategories
        ]);

        // Get Blasts for Dealer
        $blasts = $this->blasts->getAllActive($dealer->user_id);
        foreach($blasts as $single) {
            $blast = $single;
            break;
        }
        $leads = $blast->leads;
        if(count($leads) < 1) {
            throw new NoLeadsDeliverBlastException();
        }

        // Mock Text Service
        $this->mock(TextServiceInterface::class, function ($mock) use($leads, $unused, $dealer, $blast) {
            // Loop Leads to Mock Text Sent
            foreach($leads as $lead) {
                // Get From Number
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
                $from_number = $blast->from_sms_number;
                if(empty($from_number)) {
                    $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                }

                // Get Text Message
                $textMessage = $this->templates->fillTemplate($blast->template->template, [
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
        $this->artisan('text:deliver-blast ' . self::getTestDealerId())->assertExitCode(0);


        // Loop Leads
        foreach($leads as $lead) {
            // Get From Number
            $from_number = $blast->from_sms_number;
            if(empty($from_number)) {
                $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
            }

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('dealer_texts_log', [
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_phone
            ]);

            // Assert a text blast was logged sent
            $this->assertDatabaseHas('crm_text_blast_sent', [
                'text_blast_id' => $blast->id,
                'lead_id' => $lead->identifier,
                'status' => 'logged'
            ]);
        }
    }


    /**
     * Refresh Blasts in DB
     * 
     * @group CRM
     * @param int $userId
     * @return void
     */
    private function refreshBlasts($userId) {
        // Cancel All Dealer Blasts
        Blast::where('user_id', $userId)->update([
            'is_cancelled' => true
        ]);
    }

    /**
     * Refresh Blast Leads in DB
     * 
     * @group CRM
     * @param int $blastId
     * @param array $filters
     * @return array of leads outside of range
     */
    private function refreshLeads($blastId, $filters = []) {
        // Get Existing Unassigned Leads for Dealer ID
        $blast = Blast::find($blastId);

        // Get Website ID
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $websiteId = $dealer->website->id;

        // Loop Leads
        if(count($blast->leads) > 0) {
            foreach($blast->leads as $lead) {
                Lead::where('identifier', $lead->identifier)->delete();
            }
        }

        // Create 10 Leads in Last 15 Days
        for($n = 0; $n < 10; $n++) {
            // Get Random Date Since "Send After Days"
            $params = [
                'website_id' => $websiteId,
                'dealer_id' => $dealer->id,
                'date_submitted' => $this->faker->dateTimeBetween('-' . $blast->send_after_days . ' days')
            ];

            // Insert With Manufacturer or Category
            if(isset($filters['brands']) || isset($filters['categories'])) {
                // Initialize Lead Params
                $leadParams = [];

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
                    'dealer_id' => self::getTestDealerId(),
                    'tc_lead_identifier' => $lead->identifier,
                    'status' => Lead::STATUS_WON_CLOSED
                ]);
            }
        }

        // Create 5 Leads After 15 Days
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
                $leadParams = [];

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
                $params['date_submitted'] = $this->faker->dateTimeBetween('-' . ($blast->send_after_days + 10) . ' days', '-' . $blast->send_after_days . ' days');
            } else {
                $params['date_submitted'] = $this->faker->dateTimeBetween('-' . $blast->send_after_days . ' days');
            }

            // Insert Leads Into DB
            $leads[] = factory(Lead::class)->create($params);
        }
        return $leads;
    }
}
