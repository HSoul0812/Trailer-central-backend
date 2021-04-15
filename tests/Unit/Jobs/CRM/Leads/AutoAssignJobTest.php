<?php

namespace Tests\Unit\Jobs\CRM\Leads;

use App\Jobs\CRM\Leads\AutoAssignJob;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

/**
 * Test for App\Jobs\CRM\Leads\AutoAssignJob
 *
 * Class AutoAssignJobTest
 * @package Tests\Unit\Jobs\Files
 *
 * @coversDefaultClass \App\Jobs\CRM\Leads\AutoAssignJob
 */
class AutoAssignJobTest extends TestCase
{
    /**
     * @const int
     */
    const TEST_SALES_PERSON_ID = 102;

    /**
     * @const int
     */
    const TEST_ITEM_ID = 98179430;

    /**
     * @var LegacyMockInterface|AutoAssignServiceInterface
     */
    private $autoAssignServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->autoAssignServiceMock = Mockery::mock(AutoAssignServiceInterface::class);
        $this->app->instance(AutoAssignServiceInterface::class, $this->autoAssignServiceMock);
    }


    /**
     * @covers ::handle
     */
    public function testHandle()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Mock Auto Assign Lead
        $this->autoAssignServiceMock
            ->shouldReceive('autoAssign')
            ->once();

        // Initialize Auto Assign Job
        $autoAssignJob = new AutoAssignJob($lead);

        // Handle Auto Assign Job
        $result = $autoAssignJob->handle();

        // Receive Handling Auto Assign on Leads
        Log::shouldReceive('info')->with('Handling Auto Assign Manually on Lead #' . self::TEST_ITEM_ID);

        // Assert True
        $this->assertTrue($result);
    }

    /**
     * @covers ::handle
     */
    public function testHandleWithException()
    {
        // Get Dealer ID
        $dealerId = self::getTestDealerId();
        $dealerLocationId = self::getTestDealerLocationId();
        $websiteId = self::getTestWebsiteRandom();

        // Get Test Lead
        $lead = factory(Lead::class)->create([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $dealerLocationId,
            'website_id' => $websiteId,
            'inventory_id' => 0,
            'lead_type' => LeadType::TYPE_GENERAL
        ]);
        $lead->identifier = self::TEST_ITEM_ID;

        // Generate Lead Status
        $leadStatus = factory(LeadStatus::class)->make([
            'dealer_id' => self::getTestDealerId(),
            'tc_lead_identifier' => $lead->identifier,
            'sales_person_id' => self::TEST_SALES_PERSON_ID
        ]);
        $lead->setRelation('leadStatus', $leadStatus);

        // Mock Auto Assign Lead
        $this->autoAssignServiceMock
            ->shouldReceive('autoAssign')
            ->never();


        // Initialize Auto Assign Job
        $autoAssignJob = new AutoAssignJob($lead);

        // Handle Auto Assign Job
        $result = $autoAssignJob->handle();

        // Receive Handling Auto Assign on Leads
        Log::shouldReceive('error')->with('Cannot process auto assign; sales person ALREADY assigned to lead!');

        // Assert True
        $this->assertFalse($result);
    }
}
