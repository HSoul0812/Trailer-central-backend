<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Marketing\Craigslist;

use Carbon\Carbon;
use Tests\database\seeds\Marketing\Craigslist\QueueSeeder;
use Tests\TestCase;

/**
 * Class SchedulerControllerTest
 * @package Tests\Integration\Marketing\Craigslist
 */
class SchedulerControllerTest extends TestCase
{
    /**
     * @var QueueSeeder
     *
     */
    private $seeder;

    /** @var string */
    private const API_URL = '/api/marketing/clapp/scheduler';

    /** @var string */
    private const DATE_FORMAT = 'Y-m-d\TH:i:s.\0\0\0\Z';

    /**
     * Set Up Seeder
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->seeder = new QueueSeeder();
    }

    /**
     * Tear Down Seeder
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * Test that the scheduler return structure is correct
     *
     * @group Marketing
     * @typeOfTest IntegrationTestCase
     *
     * @covers \App\Http\Controllers\v1\Marketing\Craigslist\SchedulerController::index
     *
     * @return void
     */
    public function testGettingScheduler(): void
    {
        // Given that I have the necessary data in the database
        $this->seeder->seed();

        // And I build the right parameters for the request
        $query = http_build_query([
            'dealer' => $this->seeder->dealer->identifier,
            'profile_id' => $this->seeder->profile->getKey(),
            'start' => Carbon::now()->subMonths(2)->format(self::DATE_FORMAT),
            'end' => Carbon::tomorrow()->format(self::DATE_FORMAT)
        ]);

        // When I call the endpoint, I should get the desired structure
        $this->withHeaders(['access-token' => $this->seeder->authToken->access_token])
            ->json('GET', self::API_URL . "?" . $query)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'queue_id',
                        'title',
                        'inventory_id',
                        'session_id',
                        'archived',
                        'allDay',
                        'start',
                        'end',
                        'text_status',
                        'durationEditable',
                        'className',
                        'editable',
                        'color'
                    ]
                ]
            ]);
    }
}
