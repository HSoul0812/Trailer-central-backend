<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use App\Models\CRM\Leads\Lead;
use Tests\TestCase;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class AutoAssignCommandTest extends TestCase
{
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
     * Test all auto assign dealers
     *
     * @return void
     */
    public function testDealer()
    {

        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto ' . self::TEST_WEBSITE_ID)
            ->assertSuccessful()
            ->assertExitCode(0);
    }
}
