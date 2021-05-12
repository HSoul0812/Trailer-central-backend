<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Models\CRM\Email\Blast;
use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\Template;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
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
     * @const array<string> Dummy Lead Inventory Titles
     */
    const DUMMY_LEAD_INVENTORY = [
        'noreply@trailercentral.com',
        'admin@operatebeyond.com',
        'noreply@trailercentral.com',
        'info@trailercentral.com'
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

        // Mock SMTP Config
        $smtpConfig = $this->getEloquentMock(SmtpConfig::class);
        $smtpConfig->shouldReceive('fillFromSalesPerson')->once();

        // Blast Relations
        $blast->shouldReceive('setRelation')->passthru();
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

        // Validate Send Inquiry Result
        $result = $service->sendBlast($blast->email_blasts_id, $leads);

        // Assert Same
        $this->assertSame(count($result['sent']), 3);
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
