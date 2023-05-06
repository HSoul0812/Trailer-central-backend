<?php

namespace Tests\Unit\Repositories\CRM\Leads\Export;

use App\Models\CRM\Dms\Settings;
use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Services\CRM\Leads\Export\IDSService;
use App\Repositories\CRM\Leads\Export\LeadEmailRepository;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Jobs\CRM\Leads\Export\IDSJob;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class IDSServiceTest extends TestCase
{       
    const TEST_EMAIL = 'noreply@trailercentral.com';
    const TEST_SUBJECT_EMAIL = 'You have a request from your website';
    const IDS_REQUIRED_BODY_STRING = 'You received a new unit inquiry from your website. The details of the request are below';
    
    /**
     * @var App\Services\CRM\Leads\Export\IDSService
     */
    private $idsService;
    
    /**
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepository
     */
    private $leadEmailRepository;

    /**
     * @var LoggerInterface|LegacyMockInterface
     */
    protected $logMock;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->idsService = Mockery::mock(IDSServiceInterface::class);
        $this->app->instance(IDSServiceInterface::class, $this->idsService);
        
        $this->leadEmailRepository = Mockery::mock(LeadEmailRepositoryInterface::class);
        $this->app->instance(LeadEmailRepository::class, $this->leadEmailRepository);
    }

    /**
     * @group CRM
     */
    public function testExportIDSLead()
    {
        $dealerLocation = $this->getEloquentMock(DealerLocation::class);
        $lead = $this->getEloquentMock(Lead::class);
        $leadEmail = $this->getEloquentMock(LeadEmail::class);      
        $website = $this->getEloquentMock(Website::class);
        $mail = Mockery::mock('Swift_Mailer');
        $this->app['mailer']->setSwiftMailer($mail);
        
        $website->id = 1;
        
        $lead->identifier = 1;
        $lead->dealer_location_id = 1;
        $lead->dealer_id = 1;
        $lead->website_id = 1;
        
        $leadEmail->id = 1;
        $leadEmail->dealer_location_id = 1;
        $leadEmail->dealer_id = 1;
        $leadEmail->export_format = LeadEmail::EXPORT_FORMAT_IDS;     
        
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('dealerLocation')->passthru();
        $lead->shouldReceive('inventory')->passthru();
        $lead->shouldReceive('website')->passthru();
        
        $this->leadEmailRepository
                ->shouldReceive('getLeadEmailByLead')
                ->once()
                ->with($lead)
                ->andReturn($leadEmail);
        
        $leadEmail->shouldReceive('getToEmailsAttribute')
                ->once()
                ->andReturn([]);
        
        $leadEmail->shouldReceive('getCopiedEmailsAttribute')
                ->once()
                ->andReturn([]);           
        
        $mail->shouldReceive('send')
            ->andReturnUsing(function($msg) {
                $this->assertEquals(self::TEST_SUBJECT_EMAIL, $msg->getSubject());
                $this->assertEquals(self::TEST_EMAIL, $msg->getFrom());
                $this->assertContains('Some string', $msg->getBody());
            });
        
        Log::shouldReceive('channel')
            ->once();

        Log::shouldReceive('error')
            ->never();

        Log::shouldReceive('info')
            ->with('Mailing IDS Lead', ['lead' => $lead->identifier]);
        
        Log::shouldReceive('info')
            ->with('IDS Lead Mailed Successfully', ['lead' => $lead->identifier]);
        
    
        $this->expectsJobs(IDSJob::class);
        
        $this->idsService
            ->shouldReceive('export')
            ->with($lead)
            ->andReturn(true);         
        
        $service = $this->app->make(IDSService::class);

        $result = $service->export($lead);

        $this->assertTrue($result);
    }
    
    public function tearDown(): void
    {
        Mockery::close(); 
    }
    
}
