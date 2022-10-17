<?php

declare(strict_types=1);

namespace Tests\Feature\CRM\Email;

use App\Jobs\CRM\Email\ScrapeRepliesJob;
use Illuminate\Support\Facades\Queue;
use Tests\database\seeds\CRM\Email\ScrapeRepliesSeeder;
use Tests\TestCase;

/**
 * Class ScrapeRepliesTest
 * @package Tests\Feature\CRM\Email
 */
class ScrapeRepliesTest extends TestCase
{
    // Get a Random Image
    const RANDOM_IMAGE = 'https://source.unsplash.com/random;';

    /**
     * @group CRM
     * @return void
     */
    public function testScrapeReplies()
    {
        $seeder = new ScrapeRepliesSeeder();

        $seeder->seed();

        Queue::fake();

        $this->artisan('email:scrape-replies 0 0 ' . $seeder->dealer->getKey())->assertExitCode(0);

        foreach ($seeder->salesPeople as $seederSalesPerson) {
            Queue::assertPushedOn('scrapereplies', ScrapeRepliesJob::class, function (ScrapeRepliesJob $job) use ($seederSalesPerson, $seeder) {
                $seederNewDealerUser = $seeder->newDealerUser;

                $newDealerUser = $this->getFromPrivateProperty($job, 'dealer');
                $salesPerson = $this->getFromPrivateProperty($job, 'salesperson');

                return $newDealerUser->id === $seederNewDealerUser->id
                    && $newDealerUser->user_id === $seederNewDealerUser->user_id
                    && $salesPerson->id === $seederSalesPerson->id;
            });
        }

        $seeder->cleanUp();
    }

    /**
     * @group CRM
     * @return void
     */
    public function testScrapeRepliesWrongDealer()
    {
        Queue::fake();

        $this->artisan('email:scrape-replies 0 0 ' . PHP_INT_MAX)->assertExitCode(0);

        Queue::assertNotPushed('scrapereplies', ScrapeRepliesJob::class);
    }

    /**
     * @group CRM
     * @return void
     */
    public function testScrapeRepliesWithoutSalesPeople()
    {
        $seeder = new ScrapeRepliesSeeder();

        Queue::fake();

        $this->artisan('email:scrape-replies 0 0 ' . $seeder->dealer->getKey())->assertExitCode(0);

        Queue::assertNotPushed('scrapereplies', ScrapeRepliesJob::class);

        $seeder->cleanUp();
    }
}
