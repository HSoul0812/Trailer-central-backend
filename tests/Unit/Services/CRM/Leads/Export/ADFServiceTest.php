<?php

namespace Tests\Unit\Repositories\CRM\Leads\Export;

use App\Jobs\CRM\Leads\Export\ADFJob;
use App\Repositories\CRM\Leads\Export\LeadEmailRepository;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\Export\ADFService;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ADFServiceTest extends TestCase
{       
    const TEST_EMAIL = 'noreply@trailercentral.com';
    const TEST_SUBJECT_EMAIL = 'You have a request from your website';
    const ADF_REQUIRED_BODY_STRING = 'You received a new unit inquiry from your website. The details of the request are below';
    
    /**
     * @var App\Services\CRM\Leads\Export\ADFService
     */
    private $adfService;
    
    /**
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepository
     */
    private $leadEmailRepository;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->adfService = Mockery::mock(ADFServiceInterface::class);
        $this->app->instance(ADFServiceInterface::class, $this->adfService);
        
        $this->leadEmailRepository = Mockery::mock(LeadEmailRepositoryInterface::class);
        $this->app->instance(LeadEmailRepository::class, $this->leadEmailRepository);
    }
        
    public function testExportADFLead()
    {
        $dealerLocation = $this->getEloquentMock(DealerLocation::class);

        $inquiry = $this->getEloquentMock(InquiryLead::class);
        $lead = $this->getEloquentMock(Lead::class);
        $leadEmail = $this->getEloquentMock(LeadEmail::class);
        $website = $this->getEloquentMock(Website::class);
        $mail = Mockery::mock('Swift_Mailer');
        $this->app['mailer']->setSwiftMailer($mail);
        
        $website->id = 1;

        $inquiry->dealerId = 1;
        $inquiry->dealerLocationId = 1;

        $lead->identifier = 1;
        $lead->dealer_location_id = 1;
        $lead->dealer_id = 1;
        $lead->website_id = 1;
        
        $leadEmail->dealer_location_id = 1;
        $leadEmail->dealer_id = 1;
        $leadEmail->export_format = LeadEmail::EXPORT_FORMAT_ADF;     
        
        $lead->shouldReceive('setRelation')->passthru();
        $lead->shouldReceive('belongsTo')->passthru();
        $lead->shouldReceive('dealerLocation')->passthru();
        $lead->shouldReceive('inventory')->passthru();
        $lead->shouldReceive('website')->passthru();
        
        $this->leadEmailRepository
                ->shouldReceive('find')
                ->once()
                ->with($leadEmail->dealer_id, $leadEmail->dealer_location_id)
                ->andReturn($leadEmail);
        
        $inquiry->shouldReceive('getSubject')
                ->once()
                ->andReturn(self::TEST_SUBJECT_EMAIL);

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
        
        Log::shouldReceive('info')
            ->with('Mailing ADF Lead', ['lead' => $lead->identifier]);
        
        Log::shouldReceive('info')
            ->with('ADF Lead Mailed Successfully', ['lead' => $lead->identifier]);
        
    
        $this->expectsJobs(ADFJob::class);
        
        $this->adfService
            ->shouldReceive('export')
            ->with($inquiry, $lead)
            ->andReturn(true);
        
        $service = $this->app->make(ADFService::class);

        $result = $service->export($inquiry, $lead);

        $this->assertTrue($result);
    }
    
    public function tearDown(): void
    {
        Mockery::close(); 
    }
    
}
