<?php

namespace Tests\Unit\Jobs\CRM\Leads;

use App\Exceptions\CRM\Leads\AutoAssignJobSalesPersonExistsException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\LeadStatus;
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
     * @const string
     */
    const TEST_FIRST_NAME = 'Alegra';
    const TEST_LAST_NAME = 'Johnson';
    const TEST_PHONE = '555-555-5555';
    const TEST_EMAIL = 'alegra@nowhere.com';


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
     * @group CRM
     * @covers ::handle
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testHandle()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $status->sales_person_id = 0;
        $lead->leadStatus = $status;

        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();


        // Mock Auto Assign Lead
        $this->autoAssignServiceMock
            ->shouldReceive('autoAssign')
            ->once();

        // Initialize Auto Assign Job
        $autoAssignJob = new AutoAssignJob($lead);

        // Handle Auto Assign Job
        $result = $autoAssignJob->handle($this->autoAssignServiceMock);

        // Receive Handling Auto Assign on Leads
        Log::shouldReceive('info')->with('Handling Auto Assign Manually on Lead #' . $lead->identifier);

        // Assert True
        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::handle
     * @group Inquiry
     *
     * @throws BindingResolutionException
     */
    public function testHandleWithException()
    {
        // Get Model Mocks
        $lead = $this->getEloquentMock(Lead::class);
        $lead->identifier = 1;
        $lead->first_name = self::TEST_FIRST_NAME;
        $lead->last_name = self::TEST_LAST_NAME;
        $lead->phone_number = self::TEST_PHONE;
        $lead->email_address = self::TEST_EMAIL;

        $status = $this->getEloquentMock(LeadStatus::class);
        $status->sales_person_id = 1;
        $lead->leadStatus = $status;

        // Lead Relations
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('hasOne')->passthru();
        $lead->shouldReceive('leadStatus')->passthru();

        // Mock Auto Assign Lead
        $this->autoAssignServiceMock
            ->shouldReceive('autoAssign')
            ->never();

        // Expect Exception
        $this->expectException(AutoAssignJobSalesPersonExistsException::class);

        // Initialize Auto Assign Job
        $autoAssignJob = new AutoAssignJob($lead);

        // Handle Auto Assign Job
        $result = $autoAssignJob->handle($this->autoAssignServiceMock);

        // Receive Handling Auto Assign on Leads
        Log::shouldReceive('error')->with('Cannot process auto assign; sales person ALREADY assigned to lead!');

        // Assert True
        $this->assertFalse($result);
    }
}
