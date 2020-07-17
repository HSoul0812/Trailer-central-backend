<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use App\Models\CRM\Leads\Lead;
use App\Mail\AutoAssignEmail;
use TestCase;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class AutoAssignCommandTest extends TestCase
{
    use RefreshDatabase;

    // Initialize Test Constants
    const TEST_DEALER_ID = 1001;
    const TEST_LOCATION_ID = [11998, 12084, 14427];
    const TEST_WEBSITE_ID = [500, 779];
    const TEST_INVENTORY_ID = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                               1906584, 1910233, 1923231, 1925876, 1925877,
                               1925878, 1925974, 1925975, 1925977, 1927132,
                               1927133, 1928656, 1928657, 1928658, 1931524,
                               1931525, 1932625, 1932626, 1932627, 1932628];
    const TEST_FORM_TITLE = ['Value Your Trade', 'Rent to Own', 'Financing', 'Build Your Trailer'];


    /**
     * Construct Text Class
     */
    public function __construct() {
        parent::__construct();
        
        $this->createApplication();
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

        // Build Random Factory Leads
        $leads = factory(Lead::class, 10)->create();

        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $command = $this->artisan('leads:assign:auto ' . self::TEST_DEALER_ID)
                        ->expectsOutput("{$command} started {$now}")
                        ->expectsOutput("{$command} found " . count($leads) . " to process");

        // Expect End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $command->expectsOutput("{$command} finished on " . $datetime->format("l, F jS, Y"))
                ->assertExitCode(0);

        // Loop Leads
        foreach($leads as $lead) {
            // Assert a message was sent to the given leads...
            Mail::assertSent(AutoAssignMail::class, function ($mail) use ($lead) {
                return $mail->hasTo($lead->email_address);
            });
        }
    }
}
