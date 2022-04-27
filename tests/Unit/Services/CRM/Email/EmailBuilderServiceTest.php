<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Exceptions\CRM\Email\Builder\SendBuilderEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendBlastEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendCampaignEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendTemplateEmailFailedException;
use App\Exceptions\CRM\Email\Builder\FromEmailMissingSmtpConfigException;
use App\Jobs\CRM\Interactions\SendEmailBuilderJob;
use App\Mail\CRM\Interactions\EmailBuilderEmail;
use App\Models\CRM\Email\Blast;
use App\Models\CRM\Email\BlastSent;
use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\CampaignSent;
use App\Models\CRM\Email\Template;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Leads\LeadService
 *
 * Class LeadServiceTest
 * @package Tests\Unit\Services\CRM\Leads
 *
 * @coversDefaultClass \App\Services\CRM\Email\EmailBuilderService
 */
class EmailBuilderServiceTest extends TestCase
{
    /**
     * @const array<array{email: string, name: string, inventory: string}> Dummy Lead Details
     */
    const DUMMY_LEAD_DETAILS = [
        ['email' => 'noreply@trailercentral.com', 'name' => 'Trailer Central', 'inventory' => ''],
        ['email' => 'admin@operatebeyond.com', 'name' => 'Operate Beyond', 'inventory' => ''],
        ['email' => 'noreply@trailercentral.com', 'name' => 'Trailer Central', 'inventory' => ''],
        ['email' => 'info@trailercentral.com', 'name' => 'Trailer Trader', 'inventory' => '']
    ];

    /**
     * @const int Dummy Lead Set as Duplicate Email
     */
    const DUMMY_LEAD_DUP = 2;



    /**
     * @var LegacyMockInterface|BlastRepositoryInterface
     */
    private $blastRepositoryMock;

    /**
     * @var LegacyMockInterface|CampaignRepositoryInterface
     */
    private $campaignRepositoryMock;

    /**
     * @var LegacyMockInterface|TemplateRepositoryInterface
     */
    private $templateRepositoryMock;

    /**
     * @var LegacyMockInterface|LeadRepositoryInterface
     */
    private $leadRepositoryMock;

    /**
     * @var LegacyMockInterface|SalesPersonRepositoryInterface
     */
    private $salesPersonRepositoryMock;

    /**
     * @var LegacyMockInterface|InteractionsRepositoryInterface
     */
    private $interactionRepositoryMock;

    /**
     * @var LegacyMockInterface|EmailHistoryRepositoryInterface
     */
    private $emailHistoryRepositoryMock;

    /**
     * @var LegacyMockInterface|TokenRepositoryInterface
     */
    private $tokenRepositoryMock;

    /**
     * @var LegacyMockInterface|NtlmEmailServiceInterface
     */
    private $ntlmEmailServiceMock;

    /**
     * @var LegacyMockInterface|GoogleServiceInterface
     */
    private $googleServiceMock;

    /**
     * @var LegacyMockInterface|GmailServiceInterface
     */
    private $gmailServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->blastRepositoryMock = Mockery::mock(BlastRepositoryInterface::class);
        $this->app->instance(BlastRepositoryInterface::class, $this->blastRepositoryMock);

        $this->campaignRepositoryMock = Mockery::mock(CampaignRepositoryInterface::class);
        $this->app->instance(CampaignRepositoryInterface::class, $this->campaignRepositoryMock);

        $this->templateRepositoryMock = Mockery::mock(TemplateRepositoryInterface::class);
        $this->app->instance(TemplateRepositoryInterface::class, $this->templateRepositoryMock);

        $this->leadRepositoryMock = Mockery::mock(LeadRepositoryInterface::class);
        $this->app->instance(LeadRepositoryInterface::class, $this->leadRepositoryMock);

        $this->salesPersonRepositoryMock = Mockery::mock(SalesPersonRepositoryInterface::class);
        $this->app->instance(SalesPersonRepositoryInterface::class, $this->salesPersonRepositoryMock);

        $this->interactionRepositoryMock = Mockery::mock(InteractionsRepositoryInterface::class);
        $this->app->instance(InteractionsRepositoryInterface::class, $this->interactionRepositoryMock);

        $this->emailHistoryRepositoryMock = Mockery::mock(EmailHistoryRepositoryInterface::class);
        $this->app->instance(EmailHistoryRepositoryInterface::class, $this->emailHistoryRepositoryMock);

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);

        $this->ntlmEmailServiceMock = Mockery::mock(NtlmEmailServiceInterface::class);
        $this->app->instance(NtlmEmailServiceInterface::class, $this->ntlmEmailServiceMock);

        $this->googleServiceMock = Mockery::mock(GoogleServiceInterface::class);
        $this->app->instance(GoogleServiceInterface::class, $this->googleServiceMock);

        $this->gmailServiceMock = Mockery::mock(GmailServiceInterface::class);
        $this->app->instance(GmailServiceInterface::class, $this->gmailServiceMock);
    }


    /**
     * @group CRM
     * @covers ::sendBlast
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendBlast()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->html = $this->getTemplate();

        // Mock Blast
        $blast = $this->getEloquentMock(Blast::class);
        $blast->email_blasts_id = 1;
        $blast->campaign_subject = 'Test Blast';
        $blast->template = $template;
        $blast->user_id = 1;
        $blast->from_email_address = 'admin@operatebeyond.com';

        // Mock Sales Person
        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = 1;
        $salesperson->smtp_email = $blast->from_email_address;
        $salesperson->shouldReceive('getFullNameAttribute')
                    ->once()
                    ->andReturn('Operate Beyond');

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $salesperson->googleToken = $accessToken;

        // Blast Relations
        $blast->shouldReceive('setRelation')->passthru();
        $blast->shouldReceive('newDealerUser')->passthru();
        $blast->shouldReceive('belongsTo')->passthru();
        $blast->shouldReceive('hasOne')->passthru();

        // Sales Person Relations
        $salesperson->shouldReceive('setRelation')->passthru();
        $salesperson->shouldReceive('belongsTo')->passthru();
        $salesperson->shouldReceive('hasOne')->passthru();


        // Return Blast
        $this->blastRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $blast->email_blasts_id])
             ->once()
             ->andReturn($blast);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$blast->user_id, $blast->from_email_address])
             ->once()
             ->andReturn($salesperson);

        // For Each Lead!
        $leads = [];
        $leadMocks = $this->getLeadMocks();
        foreach($leadMocks as $lead) {
            // Blast Was Sent?
            $this->blastRepositoryMock
                 ->shouldReceive('wasSent')
                 ->withArgs([$blast->email_blasts_id, $lead->identifier])
                 ->once()
                 ->andReturn(false);

            // Get Lead
            $this->leadRepositoryMock
                 ->shouldReceive('get')
                 ->with(['id' => $lead->identifier])
                 ->once()
                 ->andReturn($lead);

            // Append Leads
            $leads[] = $lead->identifier;
        }

        // Expect Jobs
        $this->expectsJobs(SendEmailBuilderJob::class);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Blast Result
        $result = $service->sendBlast($blast->email_blasts_id, $leads);

        // Assert Same
        $this->assertSame($result['data']['id'], $blast->email_blasts_id);
        $this->assertSame($result['data']['type'], BuilderEmail::TYPE_BLAST);
        $this->assertSame(count($result['sent']), 3);
    }

    /**
     * @group CRM
     * @covers ::sendBlast
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendBlastInvalidEmail()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->html = $this->getTemplate();

        // Mock Blast
        $blast = $this->getEloquentMock(Blast::class);
        $blast->email_blasts_id = 1;
        $blast->campaign_subject = 'Test Blast';
        $blast->template = $template;
        $blast->user_id = 1;
        $blast->from_email_address = 'admin@operatebeyond.com';

        // Blast Relations
        $blast->shouldReceive('setRelation')->passthru();
        $blast->shouldReceive('newDealerUser')->passthru();
        $blast->shouldReceive('belongsTo')->passthru();
        $blast->shouldReceive('hasOne')->passthru();


        // Return Blast
        $this->blastRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $blast->email_blasts_id])
             ->once()
             ->andReturn($blast);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$blast->user_id, $blast->from_email_address])
             ->once()
             ->andReturn(null);

        // For Each Lead!
        $leads = [];
        $leadMocks = $this->getLeadMocks();
        foreach($leadMocks as $lead) {
            $leads[] = $lead->identifier;
        }

        // Expect Exception
        $this->expectException(FromEmailMissingSmtpConfigException::class);


        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Blast Result
        $result = $service->sendBlast($blast->email_blasts_id, $leads);

        // Assert False
        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @covers ::sendBlast
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendBlastEmailsFailed()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->html = $this->getTemplate();

        // Mock Blast
        $blast = $this->getEloquentMock(Blast::class);
        $blast->email_blasts_id = 1;
        $blast->campaign_subject = 'Test Blast';
        $blast->template = $template;
        $blast->user_id = 1;
        $blast->from_email_address = 'admin@operatebeyond.com';

        // Mock Sales Person
        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = 1;
        $salesperson->smtp_email = $blast->from_email_address;
        $salesperson->shouldReceive('getFullNameAttribute')
                    ->once()
                    ->andReturn('Operate Beyond');

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $salesperson->googleToken = $accessToken;

        // Blast Relations
        $blast->shouldReceive('setRelation')->passthru();
        $blast->shouldReceive('newDealerUser')->passthru();
        $blast->shouldReceive('belongsTo')->passthru();
        $blast->shouldReceive('hasOne')->passthru();

        // Sales Person Relations
        $salesperson->shouldReceive('setRelation')->passthru();
        $salesperson->shouldReceive('belongsTo')->passthru();
        $salesperson->shouldReceive('hasOne')->passthru();


        // Return Blast
        $this->blastRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $blast->email_blasts_id])
             ->once()
             ->andReturn($blast);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$blast->user_id, $blast->from_email_address])
             ->once()
             ->andReturn($salesperson);

        // For Each Lead!
        $leads = [];
        $leadMocks = $this->getLeadMocks();
        foreach($leadMocks as $lead) {
            // Blast Was Sent?
            $this->blastRepositoryMock
                 ->shouldReceive('wasSent')
                 ->withArgs([$blast->email_blasts_id, $lead->identifier])
                 ->once()
                 ->andReturn(false);

            // Get Lead
            $this->leadRepositoryMock
                 ->shouldReceive('get')
                 ->with(['id' => $lead->identifier])
                 ->once()
                 ->andReturn(null);

            // Append Leads
            $leads[] = $lead->identifier;
        }

        // Expect Exception
        $this->expectException(SendBuilderEmailsFailedException::class);

        // Expect Exception
        $this->expectException(SendBlastEmailsFailedException::class);


        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Blast Result
        $result = $service->sendBlast($blast->email_blasts_id, $leads);

        // Assert False
        $this->assertFalse($result);
    }


    /**
     * @group CRM
     * @covers ::sendCampaign
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendCampaign()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->html = $this->getTemplate();

        // Mock Campaign
        $campaign = $this->getEloquentMock(Campaign::class);
        $campaign->drip_campaigns_id = 1;
        $campaign->campaign_subject = 'Test Campaign';
        $campaign->template = $template;
        $campaign->user_id = 1;
        $campaign->from_email_address = 'admin@operatebeyond.com';

        // Mock Sales Person
        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = 1;
        $salesperson->smtp_email = $campaign->from_email_address;
        $salesperson->shouldReceive('getFullNameAttribute')
                    ->once()
                    ->andReturn('Operate Beyond');

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $salesperson->googleToken = $accessToken;

        // Campaign Relations
        $campaign->shouldReceive('setRelation')->passthru();
        $campaign->shouldReceive('newDealerUser')->passthru();
        $campaign->shouldReceive('belongsTo')->passthru();
        $campaign->shouldReceive('hasOne')->passthru();

        // Sales Person Relations
        $salesperson->shouldReceive('setRelation')->passthru();
        $salesperson->shouldReceive('belongsTo')->passthru();
        $salesperson->shouldReceive('hasOne')->passthru();


        // Return Campaign
        $this->campaignRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $campaign->drip_campaigns_id])
             ->once()
             ->andReturn($campaign);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$campaign->user_id, $campaign->from_email_address])
             ->once()
             ->andReturn($salesperson);

        // For Each Lead!
        $leads = [];
        $leadMocks = $this->getLeadMocks();
        foreach($leadMocks as $lead) {
            // Campaign Was Sent?
            $this->campaignRepositoryMock
                 ->shouldReceive('wasSent')
                 ->withArgs([$campaign->drip_campaigns_id, $lead->identifier])
                 ->once()
                 ->andReturn(false);

            // Get Lead
            $this->leadRepositoryMock
                 ->shouldReceive('get')
                 ->with(['id' => $lead->identifier])
                 ->once()
                 ->andReturn($lead);

            // Append Leads
            $leads[] = $lead->identifier;
        }

        // Expect Jobs
        $this->expectsJobs(SendEmailBuilderJob::class);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Campaign Result
        $result = $service->sendCampaign($campaign->drip_campaigns_id, $leads);

        // Assert Same
        $this->assertSame($result['data']['id'], $campaign->drip_campaigns_id);
        $this->assertSame($result['data']['type'], BuilderEmail::TYPE_CAMPAIGN);
        $this->assertSame(count($result['sent']), 3);
    }

    /**
     * @group CRM
     * @covers ::sendCampaign
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendCampaignInvalidEmail()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->html = $this->getTemplate();

        // Mock Campaign
        $campaign = $this->getEloquentMock(Campaign::class);
        $campaign->drip_campaigns_id = 1;
        $campaign->campaign_subject = 'Test Campaign';
        $campaign->template = $template;
        $campaign->user_id = 1;
        $campaign->from_email_address = 'admin@operatebeyond.com';

        // Campaign Relations
        $campaign->shouldReceive('setRelation')->passthru();
        $campaign->shouldReceive('newDealerUser')->passthru();
        $campaign->shouldReceive('belongsTo')->passthru();
        $campaign->shouldReceive('hasOne')->passthru();


        // Return Campaign
        $this->campaignRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $campaign->drip_campaigns_id])
             ->once()
             ->andReturn($campaign);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$campaign->user_id, $campaign->from_email_address])
             ->once()
             ->andReturn(null);

        // For Each Lead!
        $leads = [];
        $leadMocks = $this->getLeadMocks();
        foreach($leadMocks as $lead) {
            $leads[] = $lead->identifier;
        }

        // Expect Exception
        $this->expectException(FromEmailMissingSmtpConfigException::class);


        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Campaign Result
        $result = $service->sendCampaign($campaign->drip_campaigns_id, $leads);

        // Assert False
        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @covers ::sendCampaign
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendCampaignEmailsFailed()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->html = $this->getTemplate();

        // Mock Campaign
        $campaign = $this->getEloquentMock(Campaign::class);
        $campaign->drip_campaigns_id = 1;
        $campaign->campaign_subject = 'Test Campaign';
        $campaign->template = $template;
        $campaign->user_id = 1;
        $campaign->from_email_address = 'admin@operatebeyond.com';

        // Mock Sales Person
        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = 1;
        $salesperson->smtp_email = $campaign->from_email_address;
        $salesperson->shouldReceive('getFullNameAttribute')
                    ->once()
                    ->andReturn('Operate Beyond');

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $salesperson->googleToken = $accessToken;

        // Campaign Relations
        $campaign->shouldReceive('setRelation')->passthru();
        $campaign->shouldReceive('newDealerUser')->passthru();
        $campaign->shouldReceive('belongsTo')->passthru();
        $campaign->shouldReceive('hasOne')->passthru();

        // Sales Person Relations
        $salesperson->shouldReceive('setRelation')->passthru();
        $salesperson->shouldReceive('belongsTo')->passthru();
        $salesperson->shouldReceive('hasOne')->passthru();


        // Return Campaign
        $this->campaignRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $campaign->drip_campaigns_id])
             ->once()
             ->andReturn($campaign);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$campaign->user_id, $campaign->from_email_address])
             ->once()
             ->andReturn($salesperson);

        // For Each Lead!
        $leads = [];
        $leadMocks = $this->getLeadMocks();
        foreach($leadMocks as $lead) {
            // Campaign Was Sent?
            $this->campaignRepositoryMock
                 ->shouldReceive('wasSent')
                 ->withArgs([$campaign->drip_campaigns_id, $lead->identifier])
                 ->once()
                 ->andReturn(false);

            // Get Lead
            $this->leadRepositoryMock
                 ->shouldReceive('get')
                 ->with(['id' => $lead->identifier])
                 ->once()
                 ->andReturn(null);

            // Append Leads
            $leads[] = $lead->identifier;
        }

        // Expect Exception
        $this->expectException(SendBuilderEmailsFailedException::class);

        // Expect Exception
        $this->expectException(SendCampaignEmailsFailedException::class);


        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Campaign Result
        $result = $service->sendCampaign($campaign->drip_campaigns_id, $leads);

        // Assert False
        $this->assertFalse($result);
    }


    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendTemplate()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->user_id = 1;
        $template->html = $this->getTemplate();

        // Get From Email/To Email
        $subject = 'Test Template';
        $fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];

        // Mock Sales Person
        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = 1;
        $salesperson->user_id = 1;
        $salesperson->smtp_email = $fromEmail;
        $salesperson->shouldReceive('getFullNameAttribute')
                    ->once()
                    ->andReturn('Operate Beyond');

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $salesperson->googleToken = $accessToken;

        // Template Relations
        $template->shouldReceive('setRelation')->passthru();
        $template->shouldReceive('newDealerUser')->passthru();
        $template->shouldReceive('belongsTo')->passthru();
        $template->shouldReceive('hasOne')->passthru();

        // Sales Person Relations
        $salesperson->shouldReceive('setRelation')->passthru();
        $salesperson->shouldReceive('belongsTo')->passthru();
        $salesperson->shouldReceive('hasOne')->passthru();


        // Return Template
        $this->templateRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $template->template_id])
             ->once()
             ->andReturn($template);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$template->user_id, $fromEmail])
             ->once()
             ->andReturn($salesperson);

        // Expect Jobs
        $this->expectsJobs(SendEmailBuilderJob::class);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Template Result
        $result = $service->sendTemplate($template->template_id, $subject, $toEmail, 0, $fromEmail);

        // Assert Same
        $this->assertSame($result['data']['id'], $template->template_id);
        $this->assertSame($result['data']['type'], BuilderEmail::TYPE_TEMPLATE);
    }

    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendTemplateFromSalesperson()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->user_id = 1;
        $template->html = $this->getTemplate();

        // Get From Email/To Email
        $subject = 'Test Template';
        $fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];

        // Mock Sales Person
        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = 1;
        $salesperson->user_id = 1;
        $salesperson->smtp_email = $fromEmail;
        $salesperson->shouldReceive('getFullNameAttribute')
                    ->once()
                    ->andReturn('Operate Beyond');

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $salesperson->googleToken = $accessToken;

        // Template Relations
        $template->shouldReceive('setRelation')->passthru();
        $template->shouldReceive('newDealerUser')->passthru();
        $template->shouldReceive('belongsTo')->passthru();
        $template->shouldReceive('hasOne')->passthru();

        // Sales Person Relations
        $salesperson->shouldReceive('setRelation')->passthru();
        $salesperson->shouldReceive('belongsTo')->passthru();
        $salesperson->shouldReceive('hasOne')->passthru();


        // Return Template
        $this->templateRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $template->template_id])
             ->once()
             ->andReturn($template);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$template->user_id, $fromEmail])
             ->once()
             ->andReturn(null);

        // Get Sales Person By ID
        $this->salesPersonRepositoryMock
             ->shouldReceive('get')
             ->with(['sales_person_id' => $salesperson->id])
             ->once()
             ->andReturn($salesperson);

        // Expect Jobs
        $this->expectsJobs(SendEmailBuilderJob::class);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Template Result
        $result = $service->sendTemplate($template->template_id, $subject, $toEmail, $salesperson->id, $fromEmail);

        // Assert Same
        $this->assertSame($result['data']['id'], $template->template_id);
        $this->assertSame($result['data']['type'], BuilderEmail::TYPE_TEMPLATE);
    }

    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendTemplateInvalidEmail()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->user_id = 1;
        $template->html = $this->getTemplate();

        // Get From Email/To Email
        $subject = 'Test Template';
        $fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];

        // Template Relations
        $template->shouldReceive('setRelation')->passthru();
        $template->shouldReceive('newDealerUser')->passthru();
        $template->shouldReceive('belongsTo')->passthru();
        $template->shouldReceive('hasOne')->passthru();


        // Return Template
        $this->templateRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $template->template_id])
             ->once()
             ->andReturn($template);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$template->user_id, $fromEmail])
             ->once()
             ->andReturn(null);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('get')
             ->once()
             ->andReturn(null);

        // Expect Exception
        $this->expectException(FromEmailMissingSmtpConfigException::class);


        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Template Result
        $result = $service->sendTemplate($template->template_id, $subject, $toEmail, 0, $fromEmail);

        // Assert False
        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendTemplateEmailFailed()
    {
        // Mock Template
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = 1;
        $template->user_id = 1;
        $template->html = $this->getTemplate();

        // Get From Email/To Email
        $subject = 'Test Template';
        $fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];

        // Mock Sales Person
        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = 1;
        $salesperson->user_id = 1;
        $salesperson->smtp_email = $template->from_email_address;
        $salesperson->shouldReceive('getFullNameAttribute')
                    ->once()
                    ->andReturn('Operate Beyond');

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $salesperson->googleToken = $accessToken;

        // Template Relations
        $template->shouldReceive('setRelation')->passthru();
        $template->shouldReceive('newDealerUser')->passthru();
        $template->shouldReceive('belongsTo')->passthru();
        $template->shouldReceive('hasOne')->passthru();

        // Sales Person Relations
        $salesperson->shouldReceive('setRelation')->passthru();
        $salesperson->shouldReceive('belongsTo')->passthru();
        $salesperson->shouldReceive('hasOne')->passthru();


        // Return Template
        $this->templateRepositoryMock
             ->shouldReceive('get')
             ->with(['id' => $template->template_id])
             ->once()
             ->andReturn($template);

        // Get Sales Person For Email Address
        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$template->user_id, $fromEmail])
             ->once()
             ->andReturn($salesperson);

        // Expect Exception
        $this->expectException(SendBuilderEmailsFailedException::class);

        // Expect Exception
        $this->expectException(SendTemplateEmailFailedException::class);


        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Template Result
        $result = $service->sendTemplate($template->template_id, $subject, $toEmail, 0, $fromEmail);

        // Assert False
        $this->assertFalse($result);
    }


    /**
     * @group CRM
     * @covers ::saveToDb
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSaveToDb()
    {
        // Mock Interaction
        $interaction = $this->getEloquentMock(Interaction::class);
        $interaction->interaction_id = 1;

        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->leadId = 1;
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Campaign';

        // Mock Additional Fields
        $config->shouldReceive('getMessageId')
               ->once()
               ->andReturn(self::DUMMY_LEAD_DETAILS[3]['email']);
        $config->shouldReceive('getFilledTemplate')
               ->once()
               ->andReturn($this->getTemplate());
        $config->shouldReceive('getEmailHistoryParams')->passthru();

        // Mock Email History
        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = 1;
        $emailHistory->interaction_id = 1;


        // Create Interaction
        $this->interactionRepositoryMock
             ->shouldReceive('create')
             ->once()
             ->andReturn($interaction);

        // Create Email History
        $this->emailHistoryRepositoryMock
             ->shouldReceive('create')
             ->once()
             ->andReturn($emailHistory);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Save to DB Result
        $result = $service->saveToDb($config);

        // Assert Same
        $this->assertSame($result->email_id, $emailHistory->email_id);
    }

    /**
     * @group CRM
     * @covers ::saveToDb
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSaveToDbNoLead()
    {
        // Mock Interaction
        $interaction = $this->getEloquentMock(Interaction::class);
        $interaction->interaction_id = 1;

        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Campaign';

        // Mock Additional Fields
        $config->shouldReceive('getMessageId')
               ->once()
               ->andReturn(self::DUMMY_LEAD_DETAILS[3]['email']);
        $config->shouldReceive('getFilledTemplate')
               ->once()
               ->andReturn($this->getTemplate());
        $config->shouldReceive('getEmailHistoryParams')->passthru();

        // Mock Email History
        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = 1;


        // Create Interaction
        $this->interactionRepositoryMock
             ->shouldReceive('create')
             ->never();

        // Create Email History
        $this->emailHistoryRepositoryMock
             ->shouldReceive('create')
             ->once()
             ->andReturn($emailHistory);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Save to DB Result
        $result = $service->saveToDb($config);

        // Assert Same
        $this->assertSame($result->email_id, $emailHistory->email_id);
    }


    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendEmailSmtp()
    {
        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->leadId = 1;
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Campaign';
        $config->shouldReceive('isAuthTypeGmail')->once()->andReturn(false);
        $config->shouldReceive('isAuthTypeNtlm')->once()->andReturn(false);
        $config->shouldReceive('getAuthConfig')->passthru();

        // Mock SmtpConfig
        $smtpConfig = $this->getEloquentMock(SmtpConfig::class);
        $smtpConfig->authType = SmtpConfig::AUTH_AUTO;
        $config->smtpConfig = $smtpConfig;

        // Mock Additional Fields
        $config->shouldReceive('getToEmail')
               ->once()
               ->andReturn(['email' => $config->toEmail, 'name' => $config->toName]);

        // Mock Email History
        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = 1;
        $emailHistory->interaction_id = 1;

        // Mock Parsed Email
        $parsed = $this->getEloquentMock(ParsedEmail::class);
        $parsed->messageId = self::DUMMY_LEAD_DETAILS[3]['email'];
        $parsed->shouldReceive('getTo')->passthru();
        $config->shouldReceive('getParsedEmail')
               ->with($emailHistory->email_id)
               ->once()
               ->andReturn($parsed);


        Mail::fake();

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Email Result
        $result = $service->sendEmail($config, $emailHistory->email_id);

        // Assert a message was sent to the email address...
        Mail::assertSent(EmailBuilderEmail::class, function ($mail) use ($config) {
            return $mail->hasTo($config->toEmail);
        });

        // Assert Same
        $this->assertSame($result->messageId, self::DUMMY_LEAD_DETAILS[3]['email']);
    }

    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendEmailNtlm()
    {
        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->dealerId = 1;
        $config->leadId = 1;
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Campaign';
        $config->shouldReceive('isAuthTypeGmail')->once()->andReturn(false);
        $config->shouldReceive('isAuthTypeNtlm')->once()->andReturn(true);
        $config->shouldReceive('getAuthConfig')->passthru();

        // Mock SmtpConfig
        $smtpConfig = $this->getEloquentMock(SmtpConfig::class);
        $smtpConfig->authType = SmtpConfig::AUTH_NTLM;
        $config->smtpConfig = $smtpConfig;

        // Mock Email History
        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = 1;
        $emailHistory->interaction_id = 1;

        // Mock Parsed Email
        $parsed = $this->getEloquentMock(ParsedEmail::class);
        $parsed->messageId = self::DUMMY_LEAD_DETAILS[3]['email'];
        $parsed->shouldReceive('getTo')->passthru();
        $config->shouldReceive('getParsedEmail')
               ->with($emailHistory->email_id)
               ->once()
               ->andReturn($parsed);


        // Send NTLM Email
        $this->ntlmEmailServiceMock
             ->shouldReceive('send')
             ->once()
             ->andReturn($parsed);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Email Result
        $result = $service->sendEmail($config, $emailHistory->email_id);

        // Assert Same
        $this->assertSame($result->messageId, self::DUMMY_LEAD_DETAILS[3]['email']);
    }

    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendEmailGmail()
    {
        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->leadId = 1;
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Campaign';
        $config->shouldReceive('isAuthTypeGmail')->once()->andReturn(true);
        $config->shouldReceive('isAuthTypeNtlm')->never();
        $config->shouldReceive('getAuthConfig')->passthru();

        // Mock SmtpConfig
        $smtpConfig = $this->getEloquentMock(SmtpConfig::class);
        $smtpConfig->authType = SmtpConfig::AUTH_GMAIL;
        $config->smtpConfig = $smtpConfig;

        // Mock Email History
        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = 1;
        $emailHistory->interaction_id = 1;

        // Mock Parsed Email
        $parsed = $this->getEloquentMock(ParsedEmail::class);
        $parsed->messageId = self::DUMMY_LEAD_DETAILS[3]['email'];
        $parsed->shouldReceive('getTo')->passthru();
        $config->shouldReceive('getParsedEmail')
               ->with($emailHistory->email_id)
               ->once()
               ->andReturn($parsed);

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $smtpConfig->accessToken = $accessToken;
        $smtpConfig->shouldReceive('setAccessToken')->with($accessToken)->once();


        // Validate Google
        $this->googleServiceMock
             ->shouldReceive('validate')
             ->once()
             ->andReturn(null);

        // Refresh Token
        $this->tokenRepositoryMock
             ->shouldReceive('refresh')
             ->never();

        // Send Gmail Email
        $this->gmailServiceMock
             ->shouldReceive('send')
             ->withArgs([$smtpConfig, $parsed])
             ->once()
             ->andReturn($parsed);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Email Result
        $result = $service->sendEmail($config, $emailHistory->email_id);

        // Assert Same
        $this->assertSame($result->messageId, self::DUMMY_LEAD_DETAILS[3]['email']);
    }

    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testSendEmailGmailRefresh()
    {
        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->leadId = 1;
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Campaign';
        $config->shouldReceive('isAuthTypeGmail')->once()->andReturn(true);
        $config->shouldReceive('isAuthTypeNtlm')->never();
        $config->shouldReceive('getAuthConfig')->passthru();

        // Mock SmtpConfig
        $smtpConfig = $this->getEloquentMock(SmtpConfig::class);
        $smtpConfig->authType = SmtpConfig::AUTH_GMAIL;
        $config->smtpConfig = $smtpConfig;

        // Mock Email History
        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = 1;
        $emailHistory->interaction_id = 1;

        // Mock Parsed Email
        $parsed = $this->getEloquentMock(ParsedEmail::class);
        $parsed->messageId = self::DUMMY_LEAD_DETAILS[3]['email'];
        $parsed->shouldReceive('getTo')->passthru();
        $config->shouldReceive('getParsedEmail')
               ->with($emailHistory->email_id)
               ->once()
               ->andReturn($parsed);

        // Mock Access Token
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $smtpConfig->accessToken = $accessToken;
        $smtpConfig->shouldReceive('setAccessToken')->never()->with($accessToken);

        // Create New Token Mock
        $newToken = $this->getEloquentMock(AccessToken::class);
        $newToken->id = 2;
        $smtpConfig->shouldReceive('setAccessToken')->once()->with($newToken);


        // Validate Google
        $this->googleServiceMock
             ->shouldReceive('validate')
             ->once()
             ->andReturn(['new_token' => true]);

        // Refresh Token
        $this->tokenRepositoryMock
             ->shouldReceive('refresh')
             ->once()
             ->andReturn($newToken);

        // Send Gmail Email
        $this->gmailServiceMock
             ->shouldReceive('send')
             ->once()
             ->andReturn($parsed);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Email Result
        $result = $service->sendEmail($config, $emailHistory->email_id);

        // Assert Same
        $this->assertSame($result->messageId, self::DUMMY_LEAD_DETAILS[3]['email']);
    }


    /**
     * @group CRM
     * @covers ::markSent
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testMarkSentBlast()
    {
        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->id = 1;
        $config->type = BuilderEmail::TYPE_BLAST;
        $config->leadId = 1;
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Blast';

        // Mock Email History
        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = 1;

        // Mock Blast Sent
        $blastSent = $this->getEloquentMock(BlastSent::class);
        $blastSent->email_blasts_id = $config->id;
        $blastSent->lead_id = $config->leadId;
        $blastSent->message_id = self::DUMMY_LEAD_DETAILS[3]['email'];

        // Mock Parsed Email
        $parsed = $this->getEloquentMock(ParsedEmail::class);
        $parsed->emailHistoryId = 1;
        $parsed->messageId = self::DUMMY_LEAD_DETAILS[3]['email'];
        $parsed->body = $this->getTemplate();


        // Update Email History
        $this->emailHistoryRepositoryMock
             ->shouldReceive('update')
             ->once()
             ->with([
                'id' => $parsed->emailHistoryId,
                'message_id' => $parsed->messageId,
                'body' => $parsed->body,
                'date_sent' => 1
             ])
             ->andReturn($emailHistory);

        // Mark Blast as Sent
        $this->blastRepositoryMock
             ->shouldReceive('sent')
             ->once()
             ->with([
                'email_blasts_id' => $config->id,
                'lead_id' => $config->leadId,
                'message_id' => self::DUMMY_LEAD_DETAILS[3]['email']
             ])
             ->andReturn($blastSent);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Email Result
        $result = $service->markSent($config, $parsed);

        // Assert Same
        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::markSent
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testMarkSentCampaign()
    {
        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->id = 1;
        $config->type = BuilderEmail::TYPE_CAMPAIGN;
        $config->leadId = 1;
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Campaign';

        // Mock Email History
        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = 1;

        // Mock Campaign Sent
        $campaignSent = $this->getEloquentMock(CampaignSent::class);
        $campaignSent->email_blasts_id = $config->id;
        $campaignSent->lead_id = $config->leadId;
        $campaignSent->message_id = self::DUMMY_LEAD_DETAILS[3]['email'];

        // Mock Parsed Email
        $parsed = $this->getEloquentMock(ParsedEmail::class);
        $parsed->emailHistoryId = 1;
        $parsed->messageId = self::DUMMY_LEAD_DETAILS[3]['email'];
        $parsed->body = $this->getTemplate();


        // Update Email History
        $this->emailHistoryRepositoryMock
             ->shouldReceive('update')
             ->once()
             ->with([
                'id' => $parsed->emailHistoryId,
                'message_id' => $parsed->messageId,
                'body' => $parsed->body,
                'date_sent' => 1
             ])
             ->andReturn($emailHistory);

        // Mark Campaign as Sent
        $this->campaignRepositoryMock
             ->shouldReceive('sent')
             ->once()
             ->with([
                'drip_campaigns_id' => $config->id,
                'lead_id' => $config->leadId,
                'message_id' => self::DUMMY_LEAD_DETAILS[3]['email']
             ])
             ->andReturn($campaignSent);

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Email Result
        $result = $service->markSent($config, $parsed);

        // Assert Same
        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::markSent
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testMarkSentTemplate()
    {
        // Mock Builder Email
        $config = $this->getEloquentMock(BuilderEmail::class);
        $config->id = 1;
        $config->type = BuilderEmail::TYPE_TEMPLATE;
        $config->fromEmail = self::DUMMY_LEAD_DETAILS[0]['email'];
        $config->toEmail = self::DUMMY_LEAD_DETAILS[1]['email'];
        $config->toName = self::DUMMY_LEAD_DETAILS[1]['name'];
        $config->subject = 'Test Template';


        // Email History Won't Update
        $this->emailHistoryRepositoryMock
             ->shouldReceive('update')
             ->never();

        // Mark Blast as Sent Won't Run
        $this->campaignRepositoryMock
             ->shouldReceive('sent')
             ->never();

        // Mark Campaign as Sent Won't Run
        $this->campaignRepositoryMock
             ->shouldReceive('sent')
             ->never();

        // @var EmailBuilderServiceInterface $service
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        // Validate Send Email Result
        $result = $service->markSent($config);

        // Assert False
        $this->assertFalse($result);
    }



    /**
     * Get Template for Type
     * 
     * @return string
     */
    private function getTemplate() {
        return '
<html>
<body>
<p>This is a test Email!</p>
<p><strong>Full Name:</strong> {lead_name}</p>
<p><strong>Unit Interested In:</strong> {title_of_unit_of_interest}</p>
</body>
</html>
        ';
    }

    /**
     * Get Lead Mocks
     * 
     * @return array<LeadMock>
     */
    private function getLeadMocks() {
        // Get Lead Mocks
        $leadMocks = [];
        for($i = 0; $i < 4; $i++) {
            $lead = $this->getEloquentMock(Lead::class);
            $lead->identifier = $i + 1;

            // Set Details
            $details = self::DUMMY_LEAD_DETAILS[$i];
            $lead->email_address = $details['email'];
            $lead->full_name = $details['name'];
            $lead->inventory_title = $details['inventory'];
            $lead->shouldReceive('getFullNameAttribute')->andReturn($lead->full_name);
            $lead->shouldReceive('getInventoryTitleAttribute')->andReturn($lead->inventory_title);

            // Append
            $leadMocks[$i] = $lead;

            // Pass Thru
            $lead->shouldReceive('jsonSerialize')->passthru();
            $lead->shouldReceive('toArray')->passthru();
            $lead->shouldReceive('attributesToArray')->passthru();
            $lead->shouldReceive('getVisible')->passthru();
            $lead->shouldReceive('getHidden')->passthru();
            $lead->shouldReceive('getMutatedAttributes')->passthru();
            $lead->shouldReceive('cacheMutatedAttributes')->passthru();
        }

        // Return Lead Mocks
        return $leadMocks;
    }
}
