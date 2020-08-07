<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Models\CRM\Leads\Lead;
use App\Mail\AutoAssignEmail;
use Tests\TestCase;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class AutoAssignTest extends TestCase
{
    // Initialize Test Constants
    const TEST_DEALER_ID = 1001;
    const TEST_LOCATION_ID = [11998, 12084, 14427];
    const TEST_WEBSITE_ID = [500, 779];
    const TEST_FORM_TITLE = ['Value Your Trade', 'Rent to Own', 'Financing', 'Build Your Trailer'];

    /**
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    private $leads;

    /**
     * @param LeadRepositoryInterface $leads
     */
    public function __construct(LeadRepositoryInterface $leads) {
        $this->leads = $leads;
    }

    /**
     * Test all auto assign dealers
     *
     * @return void
     */
    public function testDealer()
    {
        // Initialize Time
        date_default_timezone_set(env('DB_TIMEZONE'));
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));

        // Log Start
        $now = $datetime->format("l, F jS, Y");
        $command = "leads:assign:auto " . self::TEST_DEALER_ID;

        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::TEST_DEALER_ID);

        // Get Sales People
        if(empty($salespeople)) {
            // TO DO: factory for sales people!
        }

        // Get Inventory
        if(empty($inventory)) {
            // TO DO: factory for inventory!
        }

        // Get Leads
        $leads = $this->leads->getAllUnassigned([
            'per_page' => 'all',
            'dealer_id' => $dealer->id
        ]);
        if(empty($leads)) {
            // Build Random Factory Leads
            $leads = factory(Lead::class, 10)->create();
        }

        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $console = $this->artisan('leads:assign:auto ' . self::TEST_DEALER_ID)
                        ->expectsOutput("{$command} started {$now}")
                        ->expectsOutput("{$command} found " . count($leads) . " to process");

        // Expect End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $console->expectsOutput("{$command} finished on " . $datetime->format("l, F jS, Y"))
                ->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {
            // Assert a message was sent to the given leads...
            if(!empty($dealer->crmUser->enable_assign_notification)) {
                Mail::assertSent(AutoAssignMail::class, function ($mail) use ($lead) {
                    return $mail->hasTo($lead->email_address);
                });
            }
        }
    }
}
