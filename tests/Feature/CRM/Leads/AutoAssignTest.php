<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use App\Models\CRM\Leads\Lead;
use App\Models\User\NewDealerUser;
use App\Mail\AutoAssignEmail;
use Tests\TestCase;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class AutoAssignTest extends TestCase
{
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
