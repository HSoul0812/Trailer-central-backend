<?php

namespace Tests\Unit\Repositories\CRM\Leads\Export;

use App\Exceptions\CRM\Leads\Export\InvalidToEmailAddressException;
use App\Exceptions\PropertyDoesNotExists;
use App\Jobs\CRM\Leads\Export\ADFJob;
use App\Mail\CRM\Leads\Export\ADFEmail;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Repositories\CRM\Leads\Export\LeadEmailRepository;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Repositories\User\DealerLocationRepository;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Leads\Export\ADFService;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Mockery\LegacyMockInterface;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Leads\Export\ADFService
 *
 * Class ADFServiceTest
 * @package Tests\Unit\Services\CRM\Leads\Export
 *
 * @coversDefaultClass \App\Services\CRM\Leads\Export\ADFService
 */
class ADFServiceTest extends TestCase
{
    public const TEST_EMAIL = 'No Reply TC <noreply@trailercentral.com>';
    public const TEST_EXPECTED_EMAIL = 'noreply@trailercentral.com';
    public const TEST_INVALID_EMAIL_01 = ' ';
    public const TEST_INVALID_EMAIL_02 = 'notevenanemail?';
    public const TEST_LEAD_TYPE = 'general';
    public const TEST_WEBSITE_NAME = 'Trailer Trader';
    public const ADF_REQUIRED_BODY_STRING = 'You received a new unit inquiry from your website. The details of the request are below';

    /**
     * @var ADFService
     */
    private $adfService;

    /**
     * @var LeadEmailRepository
     */
    private $leadEmailRepository;

    /**
     * @var DealerLocationRepository
     */
    private $dealerLocationRepository;

    /**
     * @var LoggerInterface|LegacyMockInterface
     */
    protected $logMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->adfService = Mockery::mock(ADFServiceInterface::class);
        $this->app->instance(ADFServiceInterface::class, $this->adfService);

        $this->leadEmailRepository = Mockery::mock(LeadEmailRepositoryInterface::class);
        $this->app->instance(LeadEmailRepositoryInterface::class, $this->leadEmailRepository);

        $this->dealerLocationRepository = Mockery::mock(DealerLocationRepositoryInterface::class);
        $this->app->instance(DealerLocationRepositoryInterface::class, $this->dealerLocationRepository);

        $this->logMock = Mockery::mock(LoggerInterface::class);
    }

    /**
     * @group CRM
     * @covers ::export
     *
     * @dataProvider validDataProvider
     *
     * @throws PropertyDoesNotExists
     * @throws BindingResolutionException
     */
    public function testExport($dealerId, $dealerLocationId, $websiteId, $inventoryId, $leadId, $emails, $expectedEmails)
    {
        Mail::fake();
        Queue::fake();

        $dealer = $this->getEloquentMock(User::class);
        $dealerLocation = $this->getEloquentMock(DealerLocation::class);
        $lead = $this->getEloquentMock(Lead::class);
        $leadEmail = $this->getEloquentMock(LeadEmail::class);
        $website = $this->getEloquentMock(Website::class);
        $inventory = $this->getEloquentMock(Inventory::class);

        $dealer->dealer_id = $dealerId;
        $dealerLocation->dealer_location_id = $dealerLocationId;

        $website->id = $websiteId;
        $website->name = self::TEST_WEBSITE_NAME;
        $inventory->inventory_id = $inventoryId;

        $lead->identifier = $leadId;
        $lead->dealer_location_id = $dealerLocationId;
        $lead->dealer_id = $dealerId;
        $lead->website_id = $websiteId;
        $lead->inventory = $inventory;
        $lead->lead_type = self::TEST_LEAD_TYPE;
        $lead->website = $website;
        $subject = $this->getLeadSubject($lead);

        $leadEmail->dealer_location_id = $dealerLocationId;
        $leadEmail->dealer_id = $dealerId;
        $leadEmail->export_format = LeadEmail::EXPORT_FORMAT_ADF;

        $this->initBelongsToRelation($lead, 'dealerLocation', $dealerLocation);
        $this->initBelongsToRelation($lead, 'inventory', $inventory);
        $this->initBelongsToRelation($lead, 'website', $website);
        $this->initBelongsToRelation($lead, 'user', $dealer);

        $this->leadEmailRepository
                ->shouldReceive('find')
                ->once()
                ->with($leadEmail->dealer_id, $leadEmail->dealer_location_id)
                ->andReturn($leadEmail);

        $this->dealerLocationRepository
                ->shouldReceive('get')
                ->once()
                ->andReturn($dealerLocation);

        $leadEmail->shouldReceive('getToEmailsAttribute')
                ->once()
                ->andReturn($emails);

        $leadEmail->shouldReceive('getCopiedEmailsAttribute')
                ->once()
                ->andReturn($emails);

        Log::shouldReceive('channel')
            ->with('leads-export')->andReturn($this->logMock);

        $this->logMock->shouldReceive('info')
            ->once()
            ->with('Mailing ADF Lead', ['lead' => $lead->identifier]);

        $this->logMock->shouldReceive('info')
            ->once()
            ->with('ADF Lead Mailed Successfully', ['lead' => $lead->identifier]);

        $lead->shouldReceive('save')->once();

        $service = $this->app->make(ADFService::class);

        $result = $service->export($lead);

        Queue::assertPushedOn('inquiry', ADFJob::class, function ($job) use ($expectedEmails) {
            $job->handle();
            return $this->getFromPrivateProperty($job, 'toEmails') == $expectedEmails
            && $this->getFromPrivateProperty($job, 'copiedEmails') == $expectedEmails;
        });
        Mail::assertSent(ADFEmail::class, function($mail) use($subject) {
            $mail->build();
            return $mail->subject == $subject && $mail->hasTo(self::TEST_EXPECTED_EMAIL);
        });

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::export
     *
     * @dataProvider invalidDataProvider
     *
     * @throws PropertyDoesNotExists
     * @throws BindingResolutionException
     */
    public function textExportWithInvalidData($dealerId, $dealerLocationId, $websiteId, $inventoryId, $leadId, $emails)
    {
        Mail::fake();
        Queue::fake();

        $dealer = $this->getEloquentMock(User::class);
        $dealerLocation = $this->getEloquentMock(DealerLocation::class);
        $lead = $this->getEloquentMock(Lead::class);
        $leadEmail = $this->getEloquentMock(LeadEmail::class);
        $website = $this->getEloquentMock(Website::class);
        $inventory = $this->getEloquentMock(Inventory::class);

        $dealer->dealer_id = $dealerId;
        $dealerLocation->dealer_location_id = $dealerLocationId;

        $website->id = $websiteId;
        $inventory->inventory_id = $inventoryId;

        $lead->identifier = $leadId;
        $lead->dealer_location_id = $dealerLocationId;
        $lead->dealer_id = $dealerId;
        $lead->website_id = $websiteId;
        $lead->inventory = $inventory;
        $lead->lead_type = self::TEST_LEAD_TYPE;

        $leadEmail->dealer_location_id = $dealerLocationId;
        $leadEmail->dealer_id = $dealerId;
        $leadEmail->export_format = LeadEmail::EXPORT_FORMAT_ADF;

        $this->initBelongsToRelation($lead, 'dealerLocation', $dealerLocation);
        $this->initBelongsToRelation($lead, 'inventory', $inventory);
        $this->initBelongsToRelation($lead, 'website', $website);
        $this->initBelongsToRelation($lead, 'user', $dealer);

        $this->leadEmailRepository
                ->shouldReceive('find')
                ->once()
                ->with($leadEmail->dealer_id, $leadEmail->dealer_location_id)
                ->andReturn($leadEmail);

        $this->dealerLocationRepository
                ->shouldReceive('get')
                ->once()
                ->andReturn($dealerLocation);

        $leadEmail->shouldReceive('getToEmailsAttribute')
                ->once()
                ->andReturn($emails);

        $leadEmail->shouldReceive('getCopiedEmailsAttribute')
                ->once()
                ->andReturn($emails);

        Log::shouldReceive('channel')
            ->with('leads-export')->andReturn($this->logMock);

        $this->logMock->shouldReceive('info')
            ->once()
            ->with('Mailing ADF Lead', ['lead' => $lead->identifier]);

        $this->logMock->shouldReceive('error')
            ->once();

        $service = $this->app->make(ADFService::class);

        $this->expectException(InvalidToEmailAddressException::class);

        $result = $service->export($lead);

        Queue::assertPushedOn('inquiry', ADFJob::class, function($job) {
            $job->handle();
            return true;
        });
        Mail::assertNothingSent();

        $this->assertTrue($result);
    }

    public function validDataProvider(): array
    {
        return [
            'Valid information' => [
                'dealer_id' => 1,
                'dealer_location_id' => 1,
                'website_id' => 1,
                'inventory_id' => 1,
                'lead_id' => 1,
                'emails' => [ self::TEST_EMAIL ],
                'expectedEmails' => [ self::TEST_EXPECTED_EMAIL ]
            ]
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            'One space email' => [
                'dealer_id' => 1,
                'dealer_location_id' => 1,
                'website_id' => 1,
                'inventory_id' => 1,
                'lead_id' => 1,
                'emails' => [ self::TEST_INVALID_EMAIL_01 ]
            ],
            'Invalid email' => [
                'dealer_id' => 1,
                'dealer_location_id' => 1,
                'website_id' => 1,
                'inventory_id' => 1,
                'lead_id' => 1,
                'emails' => [ self::TEST_INVALID_EMAIL_02 ]
            ],
        ];
    }

    /**
     * @param Lead $lead
     * @return string
     */
    private function getLeadSubject(Lead $lead): string
    {
        switch($lead->lead_type) {
            case 'call':
                $subject = "You Just Received a Click to Call From %s";
                return sprintf($subject, $lead->full_name);
            case 'inventory':
                $subject = 'Inventory Information Request on %s';
                break;
            case 'part':
                $subject = "Inventory Part Information Request on %s";
                break;
            case 'showroom':
                $subject = "Showroom Model Information Request on %s";
                break;
            case 'cta':
                $subject = "New CTA Response on %s";
                break;
            case 'sms':
                $subject = "New SMS Sent on %s";
                break;
            case 'bestprice':
                $subject = 'New Get Best Price Information Request on %s';
                break;
            default:
                $subject = 'New General Submission on %s';
                break;
        }

        // Generate subject depending on type
        return sprintf($subject, $lead->website->domain);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
