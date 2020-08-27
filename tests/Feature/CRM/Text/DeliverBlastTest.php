<?php

namespace Tests\Feature\CRM\Text;

use App\Exceptions\CRM\Text\NoLeadsTestDeliverBlastException;
use App\Services\CRM\Text\TextServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Text\Blast;
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
        $blasts = $this->blasts->getAll([
            'is_cancelled' => false,
            'is_delivered' => false,
            'send_date' => 'due_now',
            'per_page' => 'all',
            'user_id' => $dealer->user_id
        ]);
        foreach($blasts as $single) {
            $blast = $single;
            break;
        }
        $leads = $blast->leads;
        var_dump($blast);
        die;
        if(count($leads) < 1) {
            throw new NoLeadsTestDeliverBlastException();
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
                     ->withArgs([$from_number, $lead->text_number, $textMessage, $lead->full_name])
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
                     ->withArgs([$from_number, $lead->text_number, $textMessage, $lead->full_name])
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

            // Get Text Message
            $textMessage = $this->templates->fillTemplate($blast->template->template, [
                'lead_name' => $lead->full_name,
                'title_of_unit_of_interest' => $lead->inventory->title,
                'dealer_name' => $dealer->user->name
            ]);

            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('dealer_texts_log', [
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_number,
                'log_message' => $textMessage
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
     * @param int $blastId
     * @return array of leads outside of range
     */
    private function refreshLeads($blastId) {
        // Get Existing Unassigned Leads for Dealer ID
        $blast = Blast::find($blastId);

        // Loop Leads
        if(count($blast->leads) > 0) {
            foreach($blast->leads as $lead) {
                Lead::where('identifier', $lead->identifier)->delete();
            }
        }

        // Create 10 Leads in Last 15 Days
        for($n = 0; $n < 10; $n++) {
            // Get Random Date Since "Send After Days"
            factory(Lead::class)->create([
                'date_submitted' => $this->faker->dateTimeBetween('-' . $blast->send_after_days . ' days')
            ]);
        }

        // Create 5 Leads After 15 Days
        $leads = array();
        for($n = 0; $n < 5; $n++) {
            // Get Random Date Since "Send After Days"
            $leads[] = factory(Lead::class)->create([
                'date_submitted' => $this->faker->dateTimeBetween('-' . ($blast->send_after_days + 10) . ' days', '-' . $blast->send_after_days . ' days')
            ]);
        }
        return $leads;
    }
}
