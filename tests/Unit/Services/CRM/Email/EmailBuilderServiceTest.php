<?php

namespace Tests\Unit\Services\CRM\Email;

use App\Exceptions\CRM\Email\Builder\SendBlastEmailsFailedException;
use App\Exceptions\CRM\Email\Builder\SendTemplateEmailFailedException;
use App\Exceptions\CRM\Email\Builder\FromEmailMissingSmtpConfigException;
use App\Jobs\CRM\Interactions\EmailBuilderJob;
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
use App\Models\User\NewDealerUser;
use App\Models\User\User;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Repositories\CRM\Email\BounceRepositoryInterface;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\EmailBuilderService;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderStats;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\Integration\AuthServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Container\BindingResolutionException;
use League\Fractal\Manager;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Email\EmailBuilderService
 *
 * Class EmailBuilderServiceTest
 * @package Tests\Unit\Services\CRM\Email
 *
 * @coversDefaultClass \App\Services\CRM\Email\EmailBuilderService
 */
class EmailBuilderServiceTest extends TestCase
{
    /**
     * @const array<array{email: string, name: string, inventory: string}> Dummy Lead Details
     */
    const DUMMY_LEAD_DETAILS = [
        ['email' => 'noreply@trailercentral.com', 'name' => 'Trailer Central', 'inventory' => 'Some Inventory Title1'],
        ['email' => 'admin@operatebeyond.com', 'name' => 'Operate Beyond', 'inventory' => 'Some Inventory Title2'],
        ['email' => 'noreply@trailercentral.com', 'name' => 'Trailer Central', 'inventory' => 'Some Inventory Title3'],
        ['email' => 'info@trailercentral.com', 'name' => 'Trailer Trader', 'inventory' => 'Some Inventory Title4']
    ];

    /**
     * @const int Dummy Lead Set as Duplicate Email
     */
    const DUMMY_LEAD_DUP = 2;

    private const DEFAULT_FROM_EMAIL = 'default_from_email@test.com';

    private const TEMPLATE_ID = PHP_INT_MAX;
    private const TEMPLATE_USER_ID = PHP_INT_MAX - 456;

    private const DEALER_ID = PHP_INT_MAX - 111;

    private const NEW_DEALER_USER_ID = PHP_INT_MAX - 5;

    private const BLAST_ID = PHP_INT_MAX - 1;
    private const BLAST_CAMPAIGN_NAME = 'blast_campaign_name';
    private const BLAST_CAMPAIGN_SUBJECT = 'blast_campaign_subject';
    private const BLAST_USER_ID = PHP_INT_MAX - 2;
    private const BLAST_USER_FROM_EMAIL_ADDRESS = 'blast_from_email_address@test.com';

    private const CAMPAIGN_ID = PHP_INT_MAX - 123;
    private const CAMPAIGN_CAMPAIGN_SUBJECT = 'campaign_campaign_subject';
    private const CAMPAIGN_USER_ID = PHP_INT_MAX - 321;
    private const CAMPAIGN_USER_FROM_EMAIL_ADDRESS = 'campaign_from_email_address@test.com';

    private const SALES_PERSON_ID = PHP_INT_MAX - 3;
    private const SALES_PERSON_SMTP_EMAIL = 'sales_person_smtp_email_address@test.com';
    private const SALES_PERSON_FULL_NAME = 'sales_person_full_name';
    private const SALES_PERSON_SMTP_PASSWORD = 'sales_person_smtp_password';
    private const SALES_PERSON_SMTP_SERVER = 'sales_person_smtp_server';
    private const SALES_PERSON_SMTP_PORT = 111;
    private const SALES_PERSON_SMTP_SECURITY = 'sales_person_smtp_security';
    private const SALES_PERSON_SMTP_AUTH = 'sales_person_smtp_security';

    private const FIRST_LEAD_ID = PHP_INT_MAX - 6;
    private const SECOND_LEAD_ID = PHP_INT_MAX - 7;

    private const EMAIL_HISTORY_EMAIL_ID = PHP_INT_MAX - 654;

    private const PARSED_EMAIL_ID = PHP_INT_MAX - 333;

    private const SEND_TEMPLATE_SUBJECT = 'send_template_subject';
    private const SEND_TEMPLATE_HTML = 'send_template_html';
    private const SEND_TEMPLATE_TO_EMAIL = 'send_template_to_email@test.com';
    private const SEND_TEMPLATE_FROM_EMAIL = 'send_template_from_email@test.com';

    private const PARSED_EMAIL_MESSAGE_ID = 'parsed_email_message_id';

    private const INTERACTION_ID = PHP_INT_MAX - 444;

    private const ACCESS_TOKEN_ACCESS_TOKEN = 'access_token_access_token';

    /**
     * @var LegacyMockInterface|BlastRepositoryInterface
     */
    private $blastRepositoryMock;

    /**
     * @var LegacyMockInterface|CampaignRepositoryInterface
     */
    private $campaignRepositoryMock;

    /**
     * @var LegacyMockInterface|StatusRepositoryInterface
     */
    private $statusRepositoryMock;

    /**
     * @var LegacyMockInterface|TemplateRepositoryInterface
     */
    private $templateRepositoryMock;

    /**
     * @var LegacyMockInterface|BounceRepositoryInterface
     */
    private $bounceRepositoryMock;

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
     * @var LegacyMockInterface|UserRepositoryInterface
     */
    private $userRepositoryMock;

    /**
     * @var LegacyMockInterface|NtlmEmailServiceInterface
     */
    private $ntlmEmailServiceMock;

    /**
     * @var LegacyMockInterface|AuthServiceInterface
     */
    private $authServiceMock;

    /**
     * @var LegacyMockInterface|GoogleServiceInterface
     */
    private $googleServiceMock;

    /**
     * @var LegacyMockInterface|GmailServiceInterface
     */
    private $gmailServiceMock;

    /**
     * @var LegacyMockInterface|OfficeServiceInterface
     */
    private $officeServiceMock;

    /**
     * @var LegacyMockInterface|EmailBuilderService
     */
    private $emailBuilderServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->blastRepositoryMock = Mockery::mock(BlastRepositoryInterface::class);
        $this->app->instance(BlastRepositoryInterface::class, $this->blastRepositoryMock);

        $this->campaignRepositoryMock = Mockery::mock(CampaignRepositoryInterface::class);
        $this->app->instance(CampaignRepositoryInterface::class, $this->campaignRepositoryMock);

        $this->statusRepositoryMock = Mockery::mock(StatusRepositoryInterface::class);
        $this->app->instance(StatusRepositoryInterface::class, $this->statusRepositoryMock);

        $this->templateRepositoryMock = Mockery::mock(TemplateRepositoryInterface::class);
        $this->app->instance(TemplateRepositoryInterface::class, $this->templateRepositoryMock);

        $this->bounceRepositoryMock = Mockery::mock(BounceRepositoryInterface::class);
        $this->app->instance(BounceRepositoryInterface::class, $this->bounceRepositoryMock);

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

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepositoryMock);

        $this->ntlmEmailServiceMock = Mockery::mock(NtlmEmailServiceInterface::class);
        $this->app->instance(NtlmEmailServiceInterface::class, $this->ntlmEmailServiceMock);

        $this->authServiceMock = Mockery::mock(AuthServiceInterface::class);
        $this->app->instance(AuthServiceInterface::class, $this->authServiceMock);

        $this->googleServiceMock = Mockery::mock(GoogleServiceInterface::class);
        $this->app->instance(GoogleServiceInterface::class, $this->googleServiceMock);

        $this->gmailServiceMock = Mockery::mock(GmailServiceInterface::class);
        $this->app->instance(GmailServiceInterface::class, $this->gmailServiceMock);

        $this->officeServiceMock = Mockery::mock(OfficeServiceInterface::class);
        $this->app->instance(OfficeServiceInterface::class, $this->officeServiceMock);

        $this->emailBuilderServiceMock = Mockery::mock(EmailBuilderService::class, [
            $this->blastRepositoryMock,
            $this->statusRepositoryMock,
            $this->campaignRepositoryMock,
            $this->templateRepositoryMock,
            $this->bounceRepositoryMock,
            $this->leadRepositoryMock,
            $this->salesPersonRepositoryMock,
            $this->interactionRepositoryMock,
            $this->emailHistoryRepositoryMock,
            $this->tokenRepositoryMock,
            $this->userRepositoryMock,
            $this->ntlmEmailServiceMock,
            $this->authServiceMock,
            $this->googleServiceMock,
            $this->gmailServiceMock,
            $this->officeServiceMock,
            $this->app->make(Manager::class)
        ]);
    }

    /**
     * @group CRM
     * @covers ::sendBlast
     * @group EmailBuilder
     *
     * @dataProvider sendBlastDataProvider
     *
     * @param Blast|LegacyMockInterface $blast
     * @param SalesPerson|LegacyMockInterface $salesPerson
     *
     * @throws BindingResolutionException
     */
    public function testSendBlast(Blast $blast, SalesPerson $salesPerson)
    {
        $leadIds = new Collection([self::FIRST_LEAD_ID, self::SECOND_LEAD_ID]);

        $this->salesPersonRepositoryMock
             ->shouldReceive('getBySmtpEmail')
             ->withArgs([$blast->user_id, $blast->from_email_address])
             ->once()
             ->andReturn($salesPerson);

        $blast
            ->shouldReceive('getLeadIdsAttribute')
            ->twice()
            ->andReturn($leadIds);

        $this->expectsJobs(EmailBuilderJob::class);

        $this->blastRepositoryMock
            ->shouldReceive('update')
            ->with(['id' => $blast->email_blasts_id, 'delivered' => 1])
            ->once();

        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->sendBlast($blast);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('leads', $result);

        $this->assertSame($result['data']['id'], $blast->email_blasts_id);
        $this->assertSame($result['data']['type'], 'blast');
        $this->assertSame($result['data']['subject'], $blast->campaign_subject);
        $this->assertSame($result['data']['template'], $this->getTemplate());
        $this->assertSame($result['data']['template_id'], $blast->template->template_id);
        $this->assertSame($result['data']['user_id'], $blast->user_id);
        $this->assertSame($result['data']['sales_person_id'], $salesPerson->id);
        $this->assertSame($result['data']['from_email'], $blast->from_email_address);

        $this->assertIsArray($result['leads']);
        $this->assertEquals($result['leads'], $leadIds->toArray());
    }

    /**
     * @group CRM
     * @covers ::sendBlast
     * @group EmailBuilder
     *
     * @dataProvider sendBlastDataProvider
     *
     * @param Blast|LegacyMockInterface $blast
     * @param SalesPerson|LegacyMockInterface $salesPerson
     *
     * @throws BindingResolutionException
     */
    public function testSendBlastInvalidEmail(Blast $blast, SalesPerson $salesPerson)
    {
        $leadIds = new Collection([self::FIRST_LEAD_ID, self::SECOND_LEAD_ID]);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->withArgs([$blast->user_id, $blast->from_email_address])
            ->once()
            ->andReturn(null);

        $this->expectException(FromEmailMissingSmtpConfigException::class);

        $blast
            ->shouldReceive('getLeadIdsAttribute')
            ->never()
            ->andReturn($leadIds);

        $this->doesntExpectJobs(EmailBuilderJob::class);

        $this->blastRepositoryMock
            ->shouldReceive('update')
            ->never();

        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $service->sendBlast($blast);
    }

    /**
     * @group CRM
     * @covers ::sendBlast
     * @group EmailBuilder
     *
     * @dataProvider sendBlastDataProvider
     *
     * @param Blast|LegacyMockInterface $blast
     * @param SalesPerson|LegacyMockInterface $salesPerson
     *
     * @throws BindingResolutionException
     */
    public function testSendBlastWithoutFromEmailAddress(Blast $blast, SalesPerson $salesPerson)
    {
        $blast->from_email_address = null;

        Config::set('mail.from.address', self::DEFAULT_FROM_EMAIL);

        $leadIds = new Collection([self::FIRST_LEAD_ID, self::SECOND_LEAD_ID]);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->never();

        $blast
            ->shouldReceive('getLeadIdsAttribute')
            ->twice()
            ->andReturn($leadIds);

        $this->expectsJobs(EmailBuilderJob::class);

        $this->blastRepositoryMock
            ->shouldReceive('update')
            ->with(['id' => $blast->email_blasts_id, 'delivered' => 1])
            ->once();

        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->sendBlast($blast);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('leads', $result);

        $this->assertSame($result['data']['id'], $blast->email_blasts_id);
        $this->assertSame($result['data']['type'], 'blast');
        $this->assertSame($result['data']['subject'], $blast->campaign_subject);
        $this->assertSame($result['data']['template'], $this->getTemplate());
        $this->assertSame($result['data']['template_id'], $blast->template->template_id);
        $this->assertSame($result['data']['user_id'], $blast->user_id);
        $this->assertSame($result['data']['sales_person_id'], null);
        $this->assertSame($result['data']['from_email'], self::DEFAULT_FROM_EMAIL);

        $this->assertIsArray($result['leads']);
        $this->assertEquals($result['leads'], $leadIds->toArray());
    }

    /**
     * @group CRM
     * @covers ::sendBlast
     * @group EmailBuilder
     *
     * @dataProvider sendBlastDataProvider
     *
     * @param Blast|LegacyMockInterface $blast
     * @param SalesPerson|LegacyMockInterface $salesPerson
     *
     * @throws BindingResolutionException
     */
    public function testSendBlastWithException(Blast $blast, SalesPerson $salesPerson)
    {
        $leadIds = new Collection([self::FIRST_LEAD_ID, self::SECOND_LEAD_ID]);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->withArgs([$blast->user_id, $blast->from_email_address])
            ->once()
            ->andReturn($salesPerson);

        $blast
            ->shouldReceive('getLeadIdsAttribute')
            ->twice()
            ->andReturn($leadIds);

        $this->expectsJobs(EmailBuilderJob::class);

        $this->blastRepositoryMock
            ->shouldReceive('update')
            ->with(['id' => $blast->email_blasts_id, 'delivered' => 1])
            ->once()
            ->andThrow(\Exception::class);

        $this->expectException(SendBlastEmailsFailedException::class);

        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $service->sendBlast($blast);;
    }

    /**
     * @group CRM
     * @covers ::sendCampaign
     * @group EmailBuilder
     *
     * @dataProvider sendCampaignDataProvider
     *
     * @param Campaign|LegacyMockInterface $campaign
     * @param SalesPerson|LegacyMockInterface $salesPerson
     *
     * @throws BindingResolutionException
     */
    public function testSendCampaign(Campaign $campaign, SalesPerson $salesPerson)
    {
        $leads = self::FIRST_LEAD_ID . ',' . self::SECOND_LEAD_ID;
        $campaignId = $campaign->drip_campaigns_id;

        $this->campaignRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $campaignId])
            ->andReturn($campaign);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->withArgs([$campaign->user_id, $campaign->from_email_address])
            ->once()
            ->andReturn($salesPerson);

        $this->expectsJobs(EmailBuilderJob::class);

        /** @var EmailBuilderServiceInterface $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->sendCampaign($campaignId, $leads);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('leads', $result);

        $this->assertSame($result['data']['id'], $campaign->drip_campaigns_id);
        $this->assertSame($result['data']['type'], 'campaign');
        $this->assertSame($result['data']['subject'], $campaign->campaign_subject);
        $this->assertSame($result['data']['template'], $this->getTemplate());
        $this->assertSame($result['data']['template_id'], $campaign->template->template_id);
        $this->assertSame($result['data']['user_id'], $campaign->user_id);
        $this->assertSame($result['data']['sales_person_id'], $salesPerson->id);
        $this->assertSame($result['data']['from_email'], $campaign->from_email_address);

        $this->assertIsArray($result['leads']);
        $this->assertEquals($result['leads'], explode(',', $leads));
    }

    /**
     * @group CRM
     * @covers ::sendCampaign
     * @group EmailBuilder
     *
     * @dataProvider sendCampaignDataProvider
     *
     * @param Campaign|LegacyMockInterface $campaign
     * @param SalesPerson|LegacyMockInterface $salesPerson
     *
     * @throws BindingResolutionException
     */
    public function testSendCampaignInvalidEmail(Campaign $campaign, SalesPerson $salesPerson)
    {
        $leads = self::FIRST_LEAD_ID . ',' . self::SECOND_LEAD_ID;
        $campaignId = $campaign->drip_campaigns_id;

        $this->campaignRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $campaignId])
            ->andReturn($campaign);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->withArgs([$campaign->user_id, $campaign->from_email_address])
            ->once()
            ->andReturn(null);

        $this->expectException(FromEmailMissingSmtpConfigException::class);

        /** @var EmailBuilderServiceInterface $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $service->sendCampaign($campaignId, $leads);
    }

    /**
     * @group CRM
     * @covers ::sendCampaign
     * @group EmailBuilder
     *
     * @dataProvider sendCampaignDataProvider
     *
     * @param Campaign|LegacyMockInterface $campaign
     * @param SalesPerson|LegacyMockInterface $salesPerson
     *
     * @throws BindingResolutionException
     */
    public function testSendCampaignWithoutFromEmailAddress(Campaign $campaign, SalesPerson $salesPerson)
    {
        $leads = self::FIRST_LEAD_ID . ',' . self::SECOND_LEAD_ID;
        $campaignId = $campaign->drip_campaigns_id;

        $defaultFromEmail = 'default_from_email@test.com';
        Config::set('mail.from.address', $defaultFromEmail);

        $campaign->from_email_address = null;

        $this->campaignRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $campaignId])
            ->andReturn($campaign);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->never();

        $this->expectsJobs(EmailBuilderJob::class);

        /** @var EmailBuilderServiceInterface $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->sendCampaign($campaignId, $leads);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('leads', $result);

        $this->assertSame($result['data']['id'], $campaign->drip_campaigns_id);
        $this->assertSame($result['data']['type'], 'campaign');
        $this->assertSame($result['data']['subject'], $campaign->campaign_subject);
        $this->assertSame($result['data']['template'], $this->getTemplate());
        $this->assertSame($result['data']['template_id'], $campaign->template->template_id);
        $this->assertSame($result['data']['user_id'], $campaign->user_id);
        $this->assertSame($result['data']['sales_person_id'], null);
        $this->assertSame($result['data']['from_email'], $defaultFromEmail);

        $this->assertIsArray($result['leads']);
        $this->assertEquals($result['leads'], explode(',', $leads));
    }

    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @dataProvider templateDataProvider
     *
     * @param Template|LegacyMockInterface $template
     * @param SalesPerson|LegacyMockInterface $salesPerson
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param ParsedEmail $parsedEmail
     */
    public function testSendTemplateWithFromEmail(
        Template     $template,
        SalesPerson  $salesPerson,
        EmailHistory $emailHistory,
        ParsedEmail  $parsedEmail
    ) {
        $this->templateRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEMPLATE_ID])
            ->andReturn($template);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->once()
            ->with($template->user_id, self::SEND_TEMPLATE_FROM_EMAIL)
            ->andReturn($salesPerson);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($template, $salesPerson) {
                return $builder instanceof BuilderEmail
                    && $builder->id === self::TEMPLATE_ID
                    && $builder->type === 'template'
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === $template->newDealerUser->id
                    && $builder->userId === $template->user_id
                    && $builder->salesPersonId === $salesPerson->id
                    && $builder->fromEmail === $salesPerson->smtp_email
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === self::TEMPLATE_ID;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->andReturn($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->once()
            ->with(Mockery::on(function ($builder) use ($template, $salesPerson) {
                return $builder instanceof BuilderEmail
                    && $builder->id === self::TEMPLATE_ID
                    && $builder->type === 'template'
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === $template->newDealerUser->id
                    && $builder->userId === $template->user_id
                    && $builder->salesPersonId === $salesPerson->id
                    && $builder->fromEmail === $salesPerson->smtp_email
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === self::TEMPLATE_ID;
            }));

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->once()
            ->with($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendTemplate')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendTemplate(
            self::TEMPLATE_ID,
            self::SEND_TEMPLATE_SUBJECT,
            self::SEND_TEMPLATE_TO_EMAIL,
            self::SALES_PERSON_ID,
            self::SEND_TEMPLATE_FROM_EMAIL
        );

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('leads', $result);

        $this->assertSame($result['data']['id'], self::TEMPLATE_ID);
        $this->assertSame($result['data']['type'], 'template');
        $this->assertSame($result['data']['template'], $template->html);
        $this->assertSame($result['data']['subject'], self::SEND_TEMPLATE_SUBJECT);
        $this->assertSame($result['data']['template_id'], $template->template_id);
        $this->assertSame($result['data']['user_id'], $template->user_id);
        $this->assertSame($result['data']['sales_person_id'], $salesPerson->id);
        $this->assertSame($result['data']['from_email'], $salesPerson->smtp_email);

        $this->assertIsArray($result['leads']);
        $this->assertEquals($result['leads'], [self::SEND_TEMPLATE_TO_EMAIL]);
    }

    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @dataProvider templateDataProvider
     *
     * @param Template|LegacyMockInterface $template
     * @param SalesPerson|LegacyMockInterface $salesPerson
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param ParsedEmail $parsedEmail
     */
    public function testSendTemplateFromSalesperson(
        Template     $template,
        SalesPerson  $salesPerson,
        EmailHistory $emailHistory,
        ParsedEmail  $parsedEmail
    ) {
        $this->templateRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEMPLATE_ID])
            ->andReturn($template);

        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => self::SALES_PERSON_ID])
            ->andReturn($salesPerson);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($template, $salesPerson) {
                return $builder instanceof BuilderEmail
                    && $builder->id === self::TEMPLATE_ID
                    && $builder->type === 'template'
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === $template->newDealerUser->id
                    && $builder->userId === $template->user_id
                    && $builder->salesPersonId === $salesPerson->id
                    && $builder->fromEmail === $salesPerson->smtp_email
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === self::TEMPLATE_ID;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->andReturn($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->once()
            ->with(Mockery::on(function ($builder) use ($template, $salesPerson) {
                return $builder instanceof BuilderEmail
                    && $builder->id === self::TEMPLATE_ID
                    && $builder->type === 'template'
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === $template->newDealerUser->id
                    && $builder->userId === $template->user_id
                    && $builder->salesPersonId === $salesPerson->id
                    && $builder->fromEmail === $salesPerson->smtp_email
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === self::TEMPLATE_ID;
            }));

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->once()
            ->with($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendTemplate')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendTemplate(
            self::TEMPLATE_ID,
            self::SEND_TEMPLATE_SUBJECT,
            self::SEND_TEMPLATE_TO_EMAIL,
            self::SALES_PERSON_ID
        );

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('leads', $result);

        $this->assertSame($result['data']['id'], self::TEMPLATE_ID);
        $this->assertSame($result['data']['type'], 'template');
        $this->assertSame($result['data']['template'], $template->html);
        $this->assertSame($result['data']['subject'], self::SEND_TEMPLATE_SUBJECT);
        $this->assertSame($result['data']['template_id'], $template->template_id);
        $this->assertSame($result['data']['user_id'], $template->user_id);
        $this->assertSame($result['data']['sales_person_id'], $salesPerson->id);
        $this->assertSame($result['data']['from_email'], $salesPerson->smtp_email);

        $this->assertIsArray($result['leads']);
        $this->assertEquals($result['leads'], [self::SEND_TEMPLATE_TO_EMAIL]);
    }

    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @dataProvider templateDataProvider
     *
     * @param Template|LegacyMockInterface $template
     * @param SalesPerson|LegacyMockInterface $salesPerson
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param ParsedEmail $parsedEmail
     */
    public function testSendTemplateWithDefaultFromEmail(
        Template     $template,
        SalesPerson  $salesPerson,
        EmailHistory $emailHistory,
        ParsedEmail  $parsedEmail
    ) {
        Config::set('mail.from.address', self::DEFAULT_FROM_EMAIL);

        $this->templateRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEMPLATE_ID])
            ->andReturn($template);

        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($template, $salesPerson) {
                return $builder instanceof BuilderEmail
                    && $builder->id === self::TEMPLATE_ID
                    && $builder->type === 'template'
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === $template->newDealerUser->id
                    && $builder->userId === $template->user_id
                    && $builder->salesPersonId === null
                    && $builder->fromEmail === self::DEFAULT_FROM_EMAIL
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === self::TEMPLATE_ID;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->andReturn($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->once()
            ->with(Mockery::on(function ($builder) use ($template, $salesPerson) {
                return $builder instanceof BuilderEmail
                    && $builder->id === self::TEMPLATE_ID
                    && $builder->type === 'template'
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === $template->newDealerUser->id
                    && $builder->userId === $template->user_id
                    && $builder->salesPersonId === null
                    && $builder->fromEmail === self::DEFAULT_FROM_EMAIL
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === self::TEMPLATE_ID;
            }));

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->once()
            ->with($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendTemplate')
            ->passthru();

        $this->emailBuilderServiceMock
            ->shouldReceive('getDefaultFromEmail')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendTemplate(
            self::TEMPLATE_ID,
            self::SEND_TEMPLATE_SUBJECT,
            self::SEND_TEMPLATE_TO_EMAIL
        );

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('leads', $result);

        $this->assertSame($result['data']['id'], self::TEMPLATE_ID);
        $this->assertSame($result['data']['type'], 'template');
        $this->assertSame($result['data']['template'], $template->html);
        $this->assertSame($result['data']['subject'], self::SEND_TEMPLATE_SUBJECT);
        $this->assertSame($result['data']['template_id'], $template->template_id);
        $this->assertSame($result['data']['user_id'], $template->user_id);
        $this->assertSame($result['data']['sales_person_id'], null);
        $this->assertSame($result['data']['from_email'], self::DEFAULT_FROM_EMAIL);

        $this->assertIsArray($result['leads']);
        $this->assertEquals($result['leads'], [self::SEND_TEMPLATE_TO_EMAIL]);
    }

    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @dataProvider templateDataProvider
     *
     * @param Template|LegacyMockInterface $template
     * @param SalesPerson|LegacyMockInterface $salesPerson
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param ParsedEmail $parsedEmail
     */
    public function testSendTemplateInvalidEmail(
        Template     $template,
        SalesPerson  $salesPerson,
        EmailHistory $emailHistory,
        ParsedEmail  $parsedEmail
    ) {
        $this->templateRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEMPLATE_ID])
            ->andReturn($template);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->once()
            ->with($template->user_id, self::SEND_TEMPLATE_FROM_EMAIL)
            ->andReturn(null);

        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => self::SALES_PERSON_ID])
            ->andReturn(null);

        $this->expectException(FromEmailMissingSmtpConfigException::class);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendTemplate')
            ->passthru();

        $this->emailBuilderServiceMock->sendTemplate(
            self::TEMPLATE_ID,
            self::SEND_TEMPLATE_SUBJECT,
            self::SEND_TEMPLATE_TO_EMAIL,
            self::SALES_PERSON_ID,
            self::SEND_TEMPLATE_FROM_EMAIL
        );
    }

    /**
     * @group CRM
     * @covers ::sendTemplate
     * @group EmailBuilder
     *
     * @dataProvider templateDataProvider
     *
     * @param Template|LegacyMockInterface $template
     * @param SalesPerson|LegacyMockInterface $salesPerson
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param ParsedEmail $parsedEmail
     */
    public function testSendTemplateEmailFailed(
        Template     $template,
        SalesPerson  $salesPerson,
        EmailHistory $emailHistory,
        ParsedEmail  $parsedEmail
    ) {
        $this->templateRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEMPLATE_ID])
            ->andReturn($template);

        $this->salesPersonRepositoryMock
            ->shouldReceive('getBySmtpEmail')
            ->once()
            ->with($template->user_id, self::SEND_TEMPLATE_FROM_EMAIL)
            ->andReturn($salesPerson);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($template, $salesPerson) {
                return $builder instanceof BuilderEmail
                    && $builder->id === self::TEMPLATE_ID
                    && $builder->type === 'template'
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === $template->newDealerUser->id
                    && $builder->userId === $template->user_id
                    && $builder->salesPersonId === $salesPerson->id
                    && $builder->fromEmail === $salesPerson->smtp_email
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === self::TEMPLATE_ID;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->andThrow(\Exception::class);

        $this->expectException(SendTemplateEmailFailedException::class);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendTemplate')
            ->passthru();

        $this->emailBuilderServiceMock->sendTemplate(
            self::TEMPLATE_ID,
            self::SEND_TEMPLATE_SUBJECT,
            self::SEND_TEMPLATE_TO_EMAIL,
            self::SALES_PERSON_ID,
            self::SEND_TEMPLATE_FROM_EMAIL
        );
    }

    /**
     * @group CRM
     * @covers ::testTemplate
     * @group EmailBuilder
     *
     * @dataProvider templateDataProvider
     *
     * @param Template|LegacyMockInterface $template
     * @param SalesPerson|LegacyMockInterface $salesPerson
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param ParsedEmail $parsedEmail
     */
    public function testTestTemplate(
        Template     $template,
        SalesPerson  $salesPerson,
        EmailHistory $emailHistory,
        ParsedEmail  $parsedEmail
    ) {
        Config::set('mail.from.address', self::DEFAULT_FROM_EMAIL);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) {
                return $builder instanceof BuilderEmail
                    && $builder->id === 1
                    && $builder->type === 'template'
                    && $builder->template === self::SEND_TEMPLATE_HTML
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === self::DEALER_ID
                    && $builder->userId === self::NEW_DEALER_USER_ID
                    && $builder->salesPersonId === null
                    && $builder->fromEmail === self::DEFAULT_FROM_EMAIL
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === 1;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->andReturn($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->once()
            ->with(Mockery::on(function ($builder) {
                return $builder instanceof BuilderEmail
                    && $builder->id === 1
                    && $builder->type === 'template'
                    && $builder->template === self::SEND_TEMPLATE_HTML
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === self::DEALER_ID
                    && $builder->userId === self::NEW_DEALER_USER_ID
                    && $builder->salesPersonId === null
                    && $builder->fromEmail === self::DEFAULT_FROM_EMAIL
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === 1;
            }));

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->once()
            ->with($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('testTemplate')
            ->passthru();

        $this->emailBuilderServiceMock
            ->shouldReceive('getDefaultFromEmail')
            ->passthru();

        $result = $this->emailBuilderServiceMock->testTemplate(
            self::DEALER_ID,
            self::NEW_DEALER_USER_ID,
            self::SEND_TEMPLATE_SUBJECT,
            self::SEND_TEMPLATE_HTML,
            self::SEND_TEMPLATE_TO_EMAIL
        );

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('leads', $result);

        $this->assertSame($result['data']['id'], 1);
        $this->assertSame($result['data']['type'], 'template');
        $this->assertSame($result['data']['template'], self::SEND_TEMPLATE_HTML);
        $this->assertSame($result['data']['subject'], self::SEND_TEMPLATE_SUBJECT);
        $this->assertSame($result['data']['template_id'], 1);
        $this->assertSame($result['data']['user_id'], self::NEW_DEALER_USER_ID);
        $this->assertSame($result['data']['sales_person_id'], null);
        $this->assertSame($result['data']['from_email'], self::DEFAULT_FROM_EMAIL);

        $this->assertIsArray($result['leads']);
        $this->assertEquals($result['leads'], [self::SEND_TEMPLATE_TO_EMAIL]);
    }

    /**
     * @group CRM
     * @covers ::testTemplate
     * @group EmailBuilder
     *
     * @dataProvider templateDataProvider
     *
     * @param Template|LegacyMockInterface $template
     * @param SalesPerson|LegacyMockInterface $salesPerson
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param ParsedEmail $parsedEmail
     */
    public function testTestTemplateWithException(
        Template     $template,
        SalesPerson  $salesPerson,
        EmailHistory $emailHistory,
        ParsedEmail  $parsedEmail
    ) {
        Config::set('mail.from.address', self::DEFAULT_FROM_EMAIL);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) {
                return $builder instanceof BuilderEmail
                    && $builder->id === 1
                    && $builder->type === 'template'
                    && $builder->template === self::SEND_TEMPLATE_HTML
                    && $builder->subject === self::SEND_TEMPLATE_SUBJECT
                    && $builder->dealerId === self::DEALER_ID
                    && $builder->userId === self::NEW_DEALER_USER_ID
                    && $builder->salesPersonId === null
                    && $builder->fromEmail === self::DEFAULT_FROM_EMAIL
                    && $builder->toEmail === self::SEND_TEMPLATE_TO_EMAIL
                    && $builder->templateId === 1;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->andThrow(\Exception::class);

        $this->expectException(SendTemplateEmailFailedException::class);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('testTemplate')
            ->passthru();

        $this->emailBuilderServiceMock
            ->shouldReceive('getDefaultFromEmail')
            ->passthru();

        $this->emailBuilderServiceMock->testTemplate(
            self::DEALER_ID,
            self::NEW_DEALER_USER_ID,
            self::SEND_TEMPLATE_SUBJECT,
            self::SEND_TEMPLATE_HTML,
            self::SEND_TEMPLATE_TO_EMAIL
        );
    }

    /**
     * @group CRM
     * @covers ::sendEmails
     * @group EmailBuilder
     *
     * @dataProvider sendEmailsDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param Collection $leads
     * @param EmailHistory|LegacyMockInterface $emailHistory
     */
    public function testSendEmails(BuilderEmail $builderEmail, Collection $leads, EmailHistory $emailHistory, ParsedEmail $parsedEmail)
    {
        $leadIds = $leads->pluck('identifier');

        $firstLead = $leads->first();
        $secondLead = $leads->last();

        // First Lead
        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $leadIds->first()])
            ->andReturn($firstLead);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($builderEmail, $firstLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $firstLead->identifier;
            }))
            ->andReturn($emailHistory);

        $this->emailHistoryRepositoryMock
            ->shouldReceive('update')
            ->with(Mockery::on(function ($updateParams) use ($builderEmail) {
                return is_array($updateParams)
                    && isset($updateParams['id']) && $updateParams['id'] === $builderEmail->emailId
                    && isset($updateParams['message_id']) && is_string($updateParams['message_id'])
                    && isset($updateParams['body']) && strpos($updateParams['body'], $builderEmail->template) !== false
                    && isset($updateParams['was_skipped']) && $updateParams['was_skipped'] === 1
                    && isset($updateParams['date_bounced']) && $updateParams['date_bounced'] === 0
                    && isset($updateParams['date_complained']) && $updateParams['date_complained'] === 0
                    && isset($updateParams['invalid_email']) && $updateParams['invalid_email'] === 1;
            }))
            ->once();

        $this->emailBuilderServiceMock
            ->shouldReceive('markSentMessageId')
            ->once()
            ->with(
                Mockery::on(function ($builder) use ($builderEmail, $firstLead) {
                    return $builder instanceof BuilderEmail
                        && $builder->id === $builderEmail->id
                        && $builder->type === $builderEmail->type
                        && $builder->template === $builderEmail->template
                        && $builder->subject === $builderEmail->subject
                        && $builder->dealerId === $builderEmail->dealerId
                        && $builder->userId === $builderEmail->userId
                        && $builder->salesPersonId === $builderEmail->salesPersonId
                        && $builder->leadId === $firstLead->identifier;
                }),
                Mockery::on(function ($parsedEmail) use ($builderEmail, $firstLead) {
                    return $parsedEmail instanceof ParsedEmail
                        && $parsedEmail->from === $builderEmail->fromEmail
                        && $parsedEmail->subject === $builderEmail->subject
                        && $parsedEmail->emailHistoryId === $builderEmail->emailId
                        && $parsedEmail->leadId === $firstLead->identifier
                        && $parsedEmail->isHtml
                        && strpos($parsedEmail->body, $builderEmail->template) !== false
                        && $parsedEmail->subject === $builderEmail->subject;
                })
            );

        // Second Lead
        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $leadIds->last()])
            ->andReturn($secondLead);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->once();

        $this->bounceRepositoryMock
            ->shouldReceive('wasBounced')
            ->once()
            ->andReturn(false);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->andReturn($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSentMessageId')
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }), $parsedEmail)
            ->once();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmails')
            ->passthru();

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->with($parsedEmail)
            ->once();

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->withAnyArgs();

        $firstLead->shouldReceive('leadStatus')->passthru();
        $firstLead->shouldReceive('hasOne')->passthru();
        $firstLead->shouldReceive('setRelation')->passthru();
        $secondLead->shouldReceive('leadStatus')->passthru();
        $secondLead->shouldReceive('hasOne')->passthru();
        $secondLead->shouldReceive('setRelation')->passthru();

        $result = $this->emailBuilderServiceMock->sendEmails($builderEmail, $leadIds);

        $this->assertInstanceOf(BuilderStats::class, $result);

        $this->assertSame(1, $result->noSent);
        $this->assertSame(1, $result->noBounced);
        $this->assertSame(1, $result->noSkipped);
        $this->assertSame(0, $result->noDups);
        $this->assertSame(0, $result->noErrors);
    }

    /**
     * @group CRM
     * @covers ::sendEmails
     * @group EmailBuilder
     *
     * @dataProvider sendEmailsDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param Collection $leads
     * @param EmailHistory|LegacyMockInterface $emailHistory
     */
    public function testSendEmailsWithWrongLeadId(
        BuilderEmail $builderEmail,
        Collection $leads,
        EmailHistory $emailHistory,
        ParsedEmail $parsedEmail
    ) {
        $leadIds = new Collection([$leads->pluck('identifier')->first()]);

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $leadIds->first()])
            ->andThrow(\Exception::class);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->never();

        $this->emailHistoryRepositoryMock
            ->shouldReceive('update')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markSentMessageId')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->never();

        $this->bounceRepositoryMock
            ->shouldReceive('wasBounced')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmails')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmails($builderEmail, $leadIds);

        $this->assertInstanceOf(BuilderStats::class, $result);

        $this->assertSame(0, $result->noSent);
        $this->assertSame(0, $result->noBounced);
        $this->assertSame(0, $result->noSkipped);
        $this->assertSame(0, $result->noDups);
        $this->assertSame(1, $result->noErrors);
    }

    /**
     * @group CRM
     * @covers ::sendEmails
     * @group EmailBuilder
     *
     * @dataProvider sendEmailsDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param Collection $leads
     * @param EmailHistory|LegacyMockInterface $emailHistory
     */
    public function testSendEmailsTypeBlastWasSent(
        BuilderEmail $builderEmail,
        Collection $leads,
        EmailHistory $emailHistory,
        ParsedEmail $parsedEmail
    ) {
        $leadIds = new Collection([$leads->pluck('identifier')->last()]);
        $secondLead = $leads->last();

        $this->setToPrivateProperty($builderEmail, 'type', 'blast');

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $leadIds->last()])
            ->andReturn($secondLead);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->andReturn($emailHistory);

        $this->blastRepositoryMock
            ->shouldReceive('wasSent')
            ->once()
            ->with($builderEmail->id, $secondLead->email_address)
            ->andReturn(true);

        $this->blastRepositoryMock
            ->shouldReceive('wasLeadSent')
            ->once()
            ->with($builderEmail->id, $secondLead->identifier)
            ->andReturn(true);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->never();

        $this->bounceRepositoryMock
            ->shouldReceive('wasBounced')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmails')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmails($builderEmail, $leadIds);

        $this->assertInstanceOf(BuilderStats::class, $result);

        $this->assertSame(0, $result->noSent);
        $this->assertSame(0, $result->noBounced);
        $this->assertSame(1, $result->noSkipped);
        $this->assertSame(1, $result->noDups);
        $this->assertSame(0, $result->noErrors);
    }

    /**
     * @group CRM
     * @covers ::sendEmails
     * @group EmailBuilder
     *
     * @dataProvider sendEmailsDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param Collection $leads
     * @param EmailHistory|LegacyMockInterface $emailHistory
     */
    public function testSendEmailsTypeCampaignWasSent(
        BuilderEmail $builderEmail,
        Collection $leads,
        EmailHistory $emailHistory,
        ParsedEmail $parsedEmail
    ) {
        $leadIds = new Collection([$leads->pluck('identifier')->last()]);
        $secondLead = $leads->last();

        $this->setToPrivateProperty($builderEmail, 'type', 'campaign');

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $leadIds->last()])
            ->andReturn($secondLead);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->andReturn($emailHistory);

        $this->campaignRepositoryMock
            ->shouldReceive('wasSent')
            ->once()
            ->with($builderEmail->id, $secondLead->email_address)
            ->andReturn(true);

        $this->campaignRepositoryMock
            ->shouldReceive('wasLeadSent')
            ->once()
            ->with($builderEmail->id, $secondLead->identifier)
            ->andReturn(true);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->never();

        $this->bounceRepositoryMock
            ->shouldReceive('wasBounced')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmails')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmails($builderEmail, $leadIds);

        $this->assertInstanceOf(BuilderStats::class, $result);

        $this->assertSame(0, $result->noSent);
        $this->assertSame(0, $result->noBounced);
        $this->assertSame(1, $result->noSkipped);
        $this->assertSame(1, $result->noDups);
        $this->assertSame(0, $result->noErrors);
    }

    /**
     * @group CRM
     * @covers ::sendEmails
     * @group EmailBuilder
     *
     * @dataProvider sendEmailsDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param Collection $leads
     * @param EmailHistory|LegacyMockInterface $emailHistory
     */
    public function testSendEmailsEmailBounced(BuilderEmail $builderEmail, Collection $leads, EmailHistory $emailHistory, ParsedEmail $parsedEmail)
    {
        $leadIds = new Collection([$leads->pluck('identifier')->last()]);
        $secondLead = $leads->last();

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $leadIds->last()])
            ->andReturn($secondLead);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->once();

        $this->bounceRepositoryMock
            ->shouldReceive('wasBounced')
            ->once()
            ->andReturn(true);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->never();

        $this->emailHistoryRepositoryMock
            ->shouldReceive('update')
            ->with(Mockery::on(function ($updateParams) use ($builderEmail) {
                return is_array($updateParams)
                    && isset($updateParams['id']) && $updateParams['id'] === $builderEmail->emailId
                    && isset($updateParams['message_id']) && is_string($updateParams['message_id'])
                    && isset($updateParams['body']) && strpos($updateParams['body'], $builderEmail->template) !== false
                    && isset($updateParams['was_skipped']) && $updateParams['was_skipped'] === 1
                    && isset($updateParams['date_bounced']) && $updateParams['date_bounced'] === 0
                    && isset($updateParams['date_complained']) && $updateParams['date_complained'] === 0
                    && isset($updateParams['invalid_email']) && $updateParams['invalid_email'] === 0;
            }))
            ->once();

        $this->emailBuilderServiceMock
            ->shouldReceive('markSentMessageId')
            ->once()
            ->with(
                Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                    return $builder instanceof BuilderEmail
                        && $builder->id === $builderEmail->id
                        && $builder->type === $builderEmail->type
                        && $builder->template === $builderEmail->template
                        && $builder->subject === $builderEmail->subject
                        && $builder->dealerId === $builderEmail->dealerId
                        && $builder->userId === $builderEmail->userId
                        && $builder->salesPersonId === $builderEmail->salesPersonId
                        && $builder->leadId === $secondLead->identifier;
                }),
                Mockery::on(function ($parsedEmail) use ($builderEmail, $secondLead) {
                    return $parsedEmail instanceof ParsedEmail
                        && $parsedEmail->from === $builderEmail->fromEmail
                        && $parsedEmail->subject === $builderEmail->subject
                        && $parsedEmail->emailHistoryId === $builderEmail->emailId
                        && $parsedEmail->leadId === $secondLead->identifier
                        && $parsedEmail->isHtml
                        && strpos($parsedEmail->body, $builderEmail->template) !== false
                        && $parsedEmail->subject === $builderEmail->subject;
                })
            );

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmails')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmails($builderEmail, $leadIds);

        $this->assertInstanceOf(BuilderStats::class, $result);

        $this->assertSame(0, $result->noSent);
        $this->assertSame(1, $result->noBounced);
        $this->assertSame(1, $result->noSkipped);
        $this->assertSame(0, $result->noDups);
        $this->assertSame(0, $result->noErrors);
    }

    /**
     * @group CRM
     * @covers ::sendEmails
     * @group EmailBuilder
     *
     * @dataProvider sendEmailsDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param Collection $leads
     * @param EmailHistory|LegacyMockInterface $emailHistory
     */
    public function testSendEmailsWithSendEmailError(
        BuilderEmail $builderEmail,
        Collection $leads,
        EmailHistory $emailHistory,
        ParsedEmail $parsedEmail
    ) {
        $leadIds = new Collection([$leads->pluck('identifier')->last()]);
        $secondLead = $leads->last();

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $leadIds->last()])
            ->andReturn($secondLead);

        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->once()
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->andReturn($emailHistory);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->once();

        $this->bounceRepositoryMock
            ->shouldReceive('wasBounced')
            ->once()
            ->andReturn(false);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->with(Mockery::on(function ($builder) use ($builderEmail, $secondLead) {
                return $builder instanceof BuilderEmail
                    && $builder->id === $builderEmail->id
                    && $builder->type === $builderEmail->type
                    && $builder->template === $builderEmail->template
                    && $builder->subject === $builderEmail->subject
                    && $builder->dealerId === $builderEmail->dealerId
                    && $builder->userId === $builderEmail->userId
                    && $builder->salesPersonId === $builderEmail->salesPersonId
                    && $builder->leadId === $secondLead->identifier;
            }))
            ->andThrow(\Exception::class);

        $this->emailBuilderServiceMock
            ->shouldReceive('markSentMessageId')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->never();

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmails')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmails($builderEmail, $leadIds);

        $this->assertInstanceOf(BuilderStats::class, $result);

        $this->assertSame(0, $result->noSent);
        $this->assertSame(0, $result->noBounced);
        $this->assertSame(0, $result->noSkipped);
        $this->assertSame(0, $result->noDups);
        $this->assertSame(1, $result->noErrors);
    }

    /**
     * @group CRM
     * @covers ::saveToDb
     * @group EmailBuilder
     *
     * @dataProvider saveToDbDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param Interaction|LegacyMockInterface $interaction
     */
    public function testSaveToDb(BuilderEmail $builderEmail, EmailHistory $emailHistory, Interaction $interaction)
    {
        $this->interactionRepositoryMock
             ->shouldReceive('create')
             ->once()
             ->with(Mockery::on(function ($createParams) use ($builderEmail) {
                return is_array($createParams)
                    && $createParams['lead_id'] === $builderEmail->leadId
                    && $createParams['user_id'] === $builderEmail->userId
                    && $createParams['interaction_type'] === 'EMAIL'
                    && $createParams['interaction_notes'] === 'E-Mail Sent: ' . $builderEmail->subject
                    && $createParams['from_email'] === $builderEmail->fromEmail
                    && $createParams['sent_by'] === $builderEmail->fromEmail
                    && strtotime($createParams['interaction_time']) !== false;
             }))
             ->andReturn($interaction);

        $this->emailHistoryRepositoryMock
             ->shouldReceive('create')
             ->once()
             ->with(Mockery::on(function ($createParams) use ($builderEmail, $interaction) {
                 return is_array($createParams)
                    && $createParams['lead_id'] === $builderEmail->leadId
                    && $createParams['to_email'] === $builderEmail->toEmail
                    && $createParams['from_email'] === $builderEmail->fromEmail
                    && $createParams['subject'] === $builderEmail->subject
                    && $createParams['body'] === $builderEmail->template
                    && $createParams['use_html'] == true
                    && is_string($createParams['message_id'])
                    && $createParams['interaction_id'] === $interaction->interaction_id;
             }))
             ->andReturn($emailHistory);

        /** @var EmailBuilderService $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->saveToDb($builderEmail);

        $this->assertSame($result->email_id, $emailHistory->email_id);
    }

    /**
     * @group CRM
     * @covers ::saveToDb
     * @group EmailBuilder
     *
     * @dataProvider saveToDbDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param EmailHistory|LegacyMockInterface $emailHistory
     * @param Interaction|LegacyMockInterface $interaction
     */
    public function testSaveToDbNoLead(BuilderEmail $builderEmail, EmailHistory $emailHistory, Interaction $interaction)
    {
        $this->setToPrivateProperty($builderEmail, 'leadId', null);

        $this->interactionRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->emailHistoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($createParams) use ($builderEmail, $interaction) {
                return is_array($createParams)
                    && $createParams['lead_id'] === 0
                    && $createParams['to_email'] === $builderEmail->toEmail
                    && $createParams['from_email'] === $builderEmail->fromEmail
                    && $createParams['subject'] === $builderEmail->subject
                    && $createParams['body'] === $builderEmail->template
                    && $createParams['use_html'] == true
                    && is_string($createParams['message_id'])
                    && $createParams['interaction_id'] === 0;
            }))
            ->andReturn($emailHistory);

        /** @var EmailBuilderService $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->saveToDb($builderEmail);

        $this->assertSame($result->email_id, $emailHistory->email_id);
    }


    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @dataProvider sendEmailDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param SalesPerson|LegacyMockInterface $salesperson
     * @param ValidateToken $validateToken
     */
    public function testSendEmailSmtp(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, SalesPerson $salesperson, ValidateToken $validateToken)
    {
        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => $builderEmail->salesPersonId])
            ->andReturn($salesperson);

        $this->authServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($validateToken->accessToken)
            ->andReturn($validateToken);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendCustomEmail')
            ->once()
            ->with(
                Mockery::on(function ($smtpConfig) use ($builderEmail, $salesperson) {
                    return $smtpConfig instanceof SmtpConfig
                        && $smtpConfig->fromName === $salesperson->full_name
                        && $smtpConfig->password === $salesperson->smtp_password
                        && $smtpConfig->host === $salesperson->smtp_server
                        && $smtpConfig->port === $salesperson->smtp_port
                        && $smtpConfig->security === $salesperson->smtp_security
                        && $smtpConfig->authType === $salesperson->smtp_auth
                        && $smtpConfig->accessToken === $salesperson->active_token;
                }),
                $builderEmail->getToEmail(),
                Mockery::on(function ($emailBuilderEmail) use ($builderEmail) {
                    return $emailBuilderEmail instanceof EmailBuilderEmail
                        && $this->getFromPrivateProperty($emailBuilderEmail, 'parsedEmail') instanceof ParsedEmail
                        && $this->getFromPrivateProperty($emailBuilderEmail, 'subject') === $builderEmail->subject;
                })
            );

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmail($builderEmail);

        $this->assertInstanceOf(ParsedEmail::class, $result);

        $this->assertSame($builderEmail->emailId, $result->emailHistoryId);
        $this->assertSame($builderEmail->leadId, $result->leadId);
        $this->assertSame($builderEmail->toEmail, $result->to);
        $this->assertSame($builderEmail->toName, $result->toName);
        $this->assertSame($builderEmail->fromEmail, $result->from);
        $this->assertSame($builderEmail->subject, $result->subject);
        $this->assertStringContainsString($builderEmail->template, $result->body);
        $this->assertTrue($result->isHtml);
        $this->assertIsString($result->messageId);
    }

    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @dataProvider sendEmailDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param SalesPerson|LegacyMockInterface $salesperson
     * @param ValidateToken $validateToken
     */
    public function testSendEmailNtlm(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, SalesPerson $salesperson, ValidateToken $validateToken)
    {
        $salesperson->smtp_auth = SmtpConfig::AUTH_NTLM;

        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => $builderEmail->salesPersonId])
            ->andReturn($salesperson);

        $this->authServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($validateToken->accessToken)
            ->andReturn($validateToken);

        $this->ntlmEmailServiceMock
            ->shouldReceive('send')
            ->once()
            ->with(
                $builderEmail->dealerId,
                Mockery::on(function ($smtpConfig) use ($builderEmail, $salesperson) {
                    return $smtpConfig instanceof SmtpConfig
                        && $smtpConfig->fromName === $salesperson->full_name
                        && $smtpConfig->password === $salesperson->smtp_password
                        && $smtpConfig->host === $salesperson->smtp_server
                        && $smtpConfig->port === $salesperson->smtp_port
                        && $smtpConfig->security === $salesperson->smtp_security
                        && $smtpConfig->authType === $salesperson->smtp_auth
                        && $smtpConfig->accessToken === $salesperson->active_token;
                }),
                Mockery::on(function ($parsedEmail) use ($builderEmail) {
                    return $parsedEmail instanceof ParsedEmail
                        && $parsedEmail->from === $builderEmail->fromEmail
                        && $parsedEmail->subject === $builderEmail->subject
                        && $parsedEmail->emailHistoryId === $builderEmail->emailId
                        && $parsedEmail->leadId === $builderEmail->leadId
                        && $parsedEmail->isHtml
                        && strpos($parsedEmail->body, $builderEmail->template) !== false;
                })
            )
            ->andReturn($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmail($builderEmail);

        $this->assertInstanceOf(ParsedEmail::class, $result);

        $this->assertSame($parsedEmail->emailHistoryId, $result->emailHistoryId);
        $this->assertSame($parsedEmail->leadId, $result->leadId);
        $this->assertSame($parsedEmail->to, $result->to);
        $this->assertSame($parsedEmail->toName, $result->toName);
        $this->assertSame($parsedEmail->from, $result->from);
        $this->assertSame($parsedEmail->subject, $result->subject);
        $this->assertSame($parsedEmail->body, $result->body);
        $this->assertSame($parsedEmail->isHtml, $result->isHtml);
    }

    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @dataProvider sendEmailDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param SalesPerson|LegacyMockInterface $salesperson
     * @param ValidateToken $validateToken
     */
    public function testSendEmailGmail(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, SalesPerson $salesperson, ValidateToken $validateToken)
    {
        $salesperson->active_token->token_type = AccessToken::TOKEN_GOOGLE;

        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => $builderEmail->salesPersonId])
            ->andReturn($salesperson);

        $this->authServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($validateToken->accessToken)
            ->andReturn($validateToken);

        $this->gmailServiceMock
            ->shouldReceive('send')
            ->once()
            ->with(
                Mockery::on(function ($smtpConfig) use ($builderEmail, $salesperson) {
                    return $smtpConfig instanceof SmtpConfig
                        && $smtpConfig->fromName === $salesperson->full_name
                        && $smtpConfig->password === $salesperson->smtp_password
                        && $smtpConfig->host === $salesperson->smtp_server
                        && $smtpConfig->port === $salesperson->smtp_port
                        && $smtpConfig->security === $salesperson->smtp_security
                        && $smtpConfig->authType === $salesperson->smtp_auth
                        && $smtpConfig->accessToken === $salesperson->active_token;
                }),
                Mockery::on(function ($parsedEmail) use ($builderEmail) {
                    return $parsedEmail instanceof ParsedEmail
                        && $parsedEmail->from === $builderEmail->fromEmail
                        && $parsedEmail->subject === $builderEmail->subject
                        && $parsedEmail->emailHistoryId === $builderEmail->emailId
                        && $parsedEmail->leadId === $builderEmail->leadId
                        && $parsedEmail->isHtml
                        && strpos($parsedEmail->body, $builderEmail->template) !== false;
                })
            )
            ->andReturn($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmail($builderEmail);

        $this->assertInstanceOf(ParsedEmail::class, $result);

        $this->assertSame($parsedEmail->emailHistoryId, $result->emailHistoryId);
        $this->assertSame($parsedEmail->leadId, $result->leadId);
        $this->assertSame($parsedEmail->to, $result->to);
        $this->assertSame($parsedEmail->toName, $result->toName);
        $this->assertSame($parsedEmail->from, $result->from);
        $this->assertSame($parsedEmail->subject, $result->subject);
        $this->assertSame($parsedEmail->body, $result->body);
        $this->assertSame($parsedEmail->isHtml, $result->isHtml);
    }

    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @dataProvider sendEmailDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param SalesPerson|LegacyMockInterface $salesperson
     * @param ValidateToken $validateToken
     */
    public function testSendEmailOffice(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, SalesPerson $salesperson, ValidateToken $validateToken)
    {
        $salesperson->active_token->token_type = AccessToken::TOKEN_OFFICE;

        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => $builderEmail->salesPersonId])
            ->andReturn($salesperson);

        $this->authServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($validateToken->accessToken)
            ->andReturn($validateToken);

        $this->officeServiceMock
            ->shouldReceive('send')
            ->once()
            ->with(
                Mockery::on(function ($smtpConfig) use ($builderEmail, $salesperson) {
                    return $smtpConfig instanceof SmtpConfig
                        && $smtpConfig->fromName === $salesperson->full_name
                        && $smtpConfig->password === $salesperson->smtp_password
                        && $smtpConfig->host === $salesperson->smtp_server
                        && $smtpConfig->port === $salesperson->smtp_port
                        && $smtpConfig->security === $salesperson->smtp_security
                        && $smtpConfig->authType === $salesperson->smtp_auth
                        && $smtpConfig->accessToken === $salesperson->active_token;
                }),
                Mockery::on(function ($parsedEmail) use ($builderEmail) {
                    return $parsedEmail instanceof ParsedEmail
                        && $parsedEmail->from === $builderEmail->fromEmail
                        && $parsedEmail->subject === $builderEmail->subject
                        && $parsedEmail->emailHistoryId === $builderEmail->emailId
                        && $parsedEmail->leadId === $builderEmail->leadId
                        && $parsedEmail->isHtml
                        && strpos($parsedEmail->body, $builderEmail->template) !== false;
                })
            )
            ->andReturn($parsedEmail);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmail($builderEmail);

        $this->assertInstanceOf(ParsedEmail::class, $result);

        $this->assertSame($parsedEmail->emailHistoryId, $result->emailHistoryId);
        $this->assertSame($parsedEmail->leadId, $result->leadId);
        $this->assertSame($parsedEmail->to, $result->to);
        $this->assertSame($parsedEmail->toName, $result->toName);
        $this->assertSame($parsedEmail->from, $result->from);
        $this->assertSame($parsedEmail->subject, $result->subject);
        $this->assertSame($parsedEmail->body, $result->body);
        $this->assertSame($parsedEmail->isHtml, $result->isHtml);
    }

    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @dataProvider sendEmailDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param SalesPerson|LegacyMockInterface $salesperson
     * @param ValidateToken $validateToken
     */
    public function testSendEmailNotOauth(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, SalesPerson $salesperson, ValidateToken $validateToken)
    {
        /** @var SalesPerson|LegacyMockInterface $salesperson */
        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = self::SALES_PERSON_ID;
        $salesperson->smtp_email = self::SALES_PERSON_SMTP_EMAIL;
        $salesperson->smtp_password = self::SALES_PERSON_SMTP_PASSWORD;
        $salesperson->smtp_server = self::SALES_PERSON_SMTP_SERVER;
        $salesperson->smtp_port = self::SALES_PERSON_SMTP_PORT;
        $salesperson->smtp_security = self::SALES_PERSON_SMTP_SECURITY;
        $salesperson->smtp_auth = self::SALES_PERSON_SMTP_AUTH;

        $salesperson
            ->shouldReceive('getActiveTokenAttribute')
            ->andReturn(null);

        $salesperson
            ->shouldReceive('getFullNameAttribute')
            ->andReturn(self::SALES_PERSON_FULL_NAME);

        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => $builderEmail->salesPersonId])
            ->andReturn($salesperson);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendCustomEmail')
            ->once()
            ->with(
                Mockery::on(function ($smtpConfig) use ($builderEmail, $salesperson) {
                    return $smtpConfig instanceof SmtpConfig
                        && $smtpConfig->fromName === $salesperson->full_name
                        && $smtpConfig->password === $salesperson->smtp_password
                        && $smtpConfig->host === $salesperson->smtp_server
                        && $smtpConfig->port === $salesperson->smtp_port
                        && $smtpConfig->security === $salesperson->smtp_security
                        && $smtpConfig->authType === $salesperson->smtp_auth
                        && $smtpConfig->accessToken === $salesperson->active_token;
                }),
                $builderEmail->getToEmail(),
                Mockery::on(function ($emailBuilderEmail) use ($builderEmail) {
                    return $emailBuilderEmail instanceof EmailBuilderEmail
                        && $this->getFromPrivateProperty($emailBuilderEmail, 'parsedEmail') instanceof ParsedEmail
                        && $this->getFromPrivateProperty($emailBuilderEmail, 'subject') === $builderEmail->subject;
                })
            );

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmail($builderEmail);

        $this->assertInstanceOf(ParsedEmail::class, $result);

        $this->assertSame($builderEmail->emailId, $result->emailHistoryId);
        $this->assertSame($builderEmail->leadId, $result->leadId);
        $this->assertSame($builderEmail->toEmail, $result->to);
        $this->assertSame($builderEmail->toName, $result->toName);
        $this->assertSame($builderEmail->fromEmail, $result->from);
        $this->assertSame($builderEmail->subject, $result->subject);
        $this->assertStringContainsString($builderEmail->template, $result->body);
        $this->assertTrue($result->isHtml);
        $this->assertIsString($result->messageId);
    }

    /**
     * @group CRM
     * @covers ::sendEmail
     * @group EmailBuilder
     *
     * @dataProvider sendEmailDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param SalesPerson|LegacyMockInterface $salesperson
     * @param ValidateToken $validateToken
     */
    public function testSendEmailWithoutSalesPerson(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, SalesPerson $salesperson, ValidateToken $validateToken)
    {
        $user = $this->getEloquentMock(User::class);
        $user->dealer_id = $builderEmail->dealerId;

        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => $builderEmail->salesPersonId])
            ->andReturn(null);

        $this->authServiceMock
            ->shouldReceive('validate')
            ->never();

        $this->userRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $builderEmail->dealerId])
            ->andReturn($user);

        $this->emailBuilderServiceMock
            ->shouldReceive('sendCustomSesEmail')
            ->once()
            ->with(
                $user,
                $builderEmail->getToEmail(),
                Mockery::on(function ($emailBuilderEmail) use ($builderEmail) {
                    return $emailBuilderEmail instanceof EmailBuilderEmail
                        && $this->getFromPrivateProperty($emailBuilderEmail, 'parsedEmail') instanceof ParsedEmail
                        && $this->getFromPrivateProperty($emailBuilderEmail, 'subject') === $builderEmail->subject;
                })
            );

        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->passthru();

        $result = $this->emailBuilderServiceMock->sendEmail($builderEmail);

        $this->assertInstanceOf(ParsedEmail::class, $result);

        $this->assertSame($builderEmail->emailId, $result->emailHistoryId);
        $this->assertSame($builderEmail->leadId, $result->leadId);
        $this->assertSame($builderEmail->toEmail, $result->to);
        $this->assertSame($builderEmail->toName, $result->toName);
        $this->assertSame($builderEmail->fromEmail, $result->from);
        $this->assertSame($builderEmail->subject, $result->subject);
        $this->assertStringContainsString($builderEmail->template, $result->body);
        $this->assertTrue($result->isHtml);
        $this->assertEmpty($result->messageId);
    }


    /**
     * @group CRM
     * @covers ::markSent
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testMarkSentBlast(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->setToPrivateProperty($builderEmail, 'type', 'blast');

        $this->blastRepositoryMock
             ->shouldReceive('sent')
             ->once()
             ->with($builderEmail->id, $builderEmail->leadId)
             ->andReturn($blastSent);

        $this->campaignRepositoryMock
            ->shouldReceive('sent')
            ->never();

        $service = $this->app->make(EmailBuilderServiceInterface::class);
        $result = $service->markSent($builderEmail);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::markSent
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testMarkSentCampaign(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->setToPrivateProperty($builderEmail, 'type', 'campaign');

        $this->campaignRepositoryMock
            ->shouldReceive('sent')
            ->once()
            ->with($builderEmail->id, $builderEmail->leadId)
            ->andReturn($campaignSent);

        $this->blastRepositoryMock
            ->shouldReceive('sent')
            ->never();

        $service = $this->app->make(EmailBuilderServiceInterface::class);
        $result = $service->markSent($builderEmail);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::markSent
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testMarkSentTemplate(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->setToPrivateProperty($builderEmail, 'type', 'template');

        $this->campaignRepositoryMock
            ->shouldReceive('sent')
            ->never();

        $this->blastRepositoryMock
            ->shouldReceive('sent')
            ->never();

        $service = $this->app->make(EmailBuilderServiceInterface::class);
        $result = $service->markSent($builderEmail);

        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @covers ::markSentMessageId
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testMarkSentMessageIdBlast(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->setToPrivateProperty($builderEmail, 'type', 'blast');

        $this->blastRepositoryMock
            ->shouldReceive('updateSent')
            ->once()
            ->with($builderEmail->id, $builderEmail->leadId, $parsedEmail->messageId, $parsedEmail->emailHistoryId)
            ->andReturn($blastSent);

        $this->campaignRepositoryMock
            ->shouldReceive('updateSent')
            ->never();

        $service = $this->app->make(EmailBuilderServiceInterface::class);
        $result = $service->markSentMessageId($builderEmail, $parsedEmail);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::markSentMessageId
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testMarkSentMessageIdCampaign(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->setToPrivateProperty($builderEmail, 'type', 'campaign');

        $this->campaignRepositoryMock
            ->shouldReceive('updateSent')
            ->once()
            ->with($builderEmail->id, $builderEmail->leadId, $parsedEmail->messageId, $parsedEmail->emailHistoryId)
            ->andReturn($campaignSent);

        $this->blastRepositoryMock
            ->shouldReceive('updateSent')
            ->never();

        $service = $this->app->make(EmailBuilderServiceInterface::class);
        $result = $service->markSentMessageId($builderEmail, $parsedEmail);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::markSentMessageId
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testMarkSentMessageIdTemplate(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->setToPrivateProperty($builderEmail, 'type', 'template');

        $this->campaignRepositoryMock
            ->shouldReceive('updateSent')
            ->never();

        $this->blastRepositoryMock
            ->shouldReceive('updateSent')
            ->never();

        $service = $this->app->make(EmailBuilderServiceInterface::class);
        $result = $service->markSentMessageId($builderEmail, $parsedEmail);

        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @covers ::markEmailSent
     * @group EmailBuilder
     */
    public function testMarkEmailSent()
    {
        $parsedEmail = new ParsedEmail([
            'emailHistoryId' => self::PARSED_EMAIL_ID,
            'messageId' => self::PARSED_EMAIL_MESSAGE_ID,
            'body' => self::SEND_TEMPLATE_HTML,
        ]);

        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = self::EMAIL_HISTORY_EMAIL_ID;

        $this->emailHistoryRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with([
                'id' => $parsedEmail->emailHistoryId,
                'message_id' => $parsedEmail->messageId,
                'body' => $parsedEmail->body,
                'date_sent' => 1
            ])
            ->andReturn($emailHistory);

        /** @var EmailBuilderService $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);
        $result = $service->markEmailSent($parsedEmail);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::replaceMessageId
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testReplaceMessageIdBlast(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->blastRepositoryMock
            ->shouldReceive('updateSent')
            ->once()
            ->with($builderEmail->id, $builderEmail->leadId, $parsedEmail->messageId, $parsedEmail->emailHistoryId)
            ->andReturn($blastSent);

        $this->campaignRepositoryMock
            ->shouldReceive('updateSent')
            ->never();

        $this->emailHistoryRepositoryMock
            ->shouldReceive('update')
            ->with([
                'id' => $parsedEmail->emailHistoryId,
                'message_id' => $parsedEmail->messageId,
                'date_sent' => 1
            ])
            ->once();

        /** @var EmailBuilderService $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->replaceMessageId(
            'blast',
            $builderEmail->id,
            $builderEmail->leadId,
            $parsedEmail->emailHistoryId,
            $parsedEmail->messageId
        );

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::replaceMessageId
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testReplaceMessageIdCampaign(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->campaignRepositoryMock
            ->shouldReceive('updateSent')
            ->once()
            ->with($builderEmail->id, $builderEmail->leadId, $parsedEmail->messageId, $parsedEmail->emailHistoryId)
            ->andReturn($campaignSent);

        $this->blastRepositoryMock
            ->shouldReceive('updateSent')
            ->never();

        $this->emailHistoryRepositoryMock
            ->shouldReceive('update')
            ->with([
                'id' => $parsedEmail->emailHistoryId,
                'message_id' => $parsedEmail->messageId,
                'date_sent' => 1
            ])
            ->once();

        /** @var EmailBuilderService $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->replaceMessageId(
            'campaign',
            $builderEmail->id,
            $builderEmail->leadId,
            $parsedEmail->emailHistoryId,
            $parsedEmail->messageId
        );

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::replaceMessageId
     * @group EmailBuilder
     *
     * @dataProvider markSentDataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param BlastSent|LegacyMockInterface $blastSent
     * @param CampaignSent|LegacyMockInterface $campaignSent
     */
    public function testReplaceMessageIdTemplate(BuilderEmail $builderEmail, ParsedEmail $parsedEmail, BlastSent $blastSent, CampaignSent $campaignSent)
    {
        $this->campaignRepositoryMock
            ->shouldReceive('updateSent')
            ->never();

        $this->blastRepositoryMock
            ->shouldReceive('updateSent')
            ->never();

        $this->emailHistoryRepositoryMock
            ->shouldReceive('update')
            ->with([
                'id' => $parsedEmail->emailHistoryId,
                'message_id' => $parsedEmail->messageId,
                'date_sent' => 1
            ])
            ->once();

        /** @var EmailBuilderService $service */
        $service = $this->app->make(EmailBuilderServiceInterface::class);

        $result = $service->replaceMessageId(
            'template',
            $builderEmail->id,
            $builderEmail->leadId,
            $parsedEmail->emailHistoryId,
            $parsedEmail->messageId
        );

        $this->assertFalse($result);
    }

    /**
     * @return array[]
     */
    public function sendBlastDataProvider(): array
    {
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = self::TEMPLATE_ID;
        $template->html = $this->getTemplate();

        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
        $newDealerUser->id = self::NEW_DEALER_USER_ID;

        $blast = $this->getEloquentMock(Blast::class);
        $blast->email_blasts_id = self::BLAST_ID;
        $blast->campaign_subject = self::BLAST_CAMPAIGN_SUBJECT;
        $blast->campaign_name = self::BLAST_CAMPAIGN_NAME;
        $blast->user_id = self::BLAST_USER_ID;
        $blast->from_email_address = self::BLAST_USER_FROM_EMAIL_ADDRESS;

        $this->initBelongsToRelation($blast, 'template', $template);
        $this->initBelongsToRelation($blast, 'newDealerUser', $newDealerUser);

        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = self::SALES_PERSON_ID;

        return [[$blast, $salesperson]];
    }

    /**
     * @return array[]
     */
    public function sendCampaignDataProvider(): array
    {
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = self::TEMPLATE_ID;
        $template->html = $this->getTemplate();

        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
        $newDealerUser->id = self::NEW_DEALER_USER_ID;

        $campaign = $this->getEloquentMock(Campaign::class);
        $campaign->drip_campaigns_id = self::CAMPAIGN_ID;
        $campaign->campaign_subject = self::CAMPAIGN_CAMPAIGN_SUBJECT;
        $campaign->user_id = self::CAMPAIGN_USER_ID;
        $campaign->from_email_address = self::CAMPAIGN_USER_FROM_EMAIL_ADDRESS;

        $this->initBelongsToRelation($campaign, 'template', $template);
        $this->initBelongsToRelation($campaign, 'newDealerUser', $newDealerUser);

        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = self::SALES_PERSON_ID;

        return [[$campaign, $salesperson]];
    }

    /**
     * @return array[]
     */
    public function templateDataProvider(): array
    {
        $template = $this->getEloquentMock(Template::class);
        $template->template_id = self::TEMPLATE_ID;
        $template->html = $this->getTemplate();
        $template->user_id = self::TEMPLATE_USER_ID;

        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
        $newDealerUser->id = self::NEW_DEALER_USER_ID;

        $this->initBelongsToRelation($template, 'newDealerUser', $newDealerUser);

        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = self::SALES_PERSON_ID;
        $salesperson->smtp_email = self::SALES_PERSON_SMTP_EMAIL;

        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = self::EMAIL_HISTORY_EMAIL_ID;

        $parsedEmail = new ParsedEmail(['id' => self::PARSED_EMAIL_ID]);

        return [[$template, $salesperson, $emailHistory, $parsedEmail]];
    }

    /**
     * @return array[]
     */
    public function sendEmailsDataProvider(): array
    {
        $builderEmail = new BuilderEmail([
            'id' => self::TEMPLATE_ID,
            'type' => 'template',
            'template' => self::SEND_TEMPLATE_HTML,
            'subject' => self::SEND_TEMPLATE_SUBJECT,
            'dealerId' => self::DEALER_ID,
            'userId' => self::NEW_DEALER_USER_ID,
            'salesPersonId' => self::SALES_PERSON_ID,
            'fromEmail' => self::SALES_PERSON_SMTP_EMAIL,
            'toEmail' => self::SEND_TEMPLATE_TO_EMAIL,
            'templateId' => self::TEMPLATE_ID
        ]);

        $leadMocks = [];

        for($i = 0; $i < 2; $i++) {
            $lead = $this->getEloquentMock(Lead::class);
            $lead->identifier = self::FIRST_LEAD_ID + $i + 1;

            $details = self::DUMMY_LEAD_DETAILS[$i];
            $lead->email_address = $details['email'];
            $lead->full_name = $details['name'];
            $lead->inventory_title = $details['inventory'];
            $lead->shouldReceive('getFullNameAttribute')->andReturn($lead->full_name);
            $lead->shouldReceive('getInventoryTitleAttribute')->andReturn($lead->inventory_title);

            $leadMocks[$i] = $lead;
        }

        $leadMocks[0]->email_address = '';

        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = self::EMAIL_HISTORY_EMAIL_ID;

        $parsedEmail = new ParsedEmail(['messageId' => self::PARSED_EMAIL_MESSAGE_ID]);

        return  [[$builderEmail, new Collection($leadMocks), $emailHistory ,$parsedEmail]];
    }

    /**
     * @return array[]
     */
    public function saveToDbDataProvider(): array
    {
        $builderEmail = new BuilderEmail([
            'id' => self::TEMPLATE_ID,
            'type' => 'template',
            'template' => self::SEND_TEMPLATE_HTML,
            'subject' => self::SEND_TEMPLATE_SUBJECT,
            'dealerId' => self::DEALER_ID,
            'userId' => self::NEW_DEALER_USER_ID,
            'salesPersonId' => self::SALES_PERSON_ID,
            'fromEmail' => self::SALES_PERSON_SMTP_EMAIL,
            'toEmail' => self::SEND_TEMPLATE_TO_EMAIL,
            'templateId' => self::TEMPLATE_ID,
            'leadId' => self::FIRST_LEAD_ID
        ]);

        $emailHistory = $this->getEloquentMock(EmailHistory::class);
        $emailHistory->email_id = self::EMAIL_HISTORY_EMAIL_ID;
        $emailHistory->interaction_id = self::INTERACTION_ID;

        $interaction = $this->getEloquentMock(Interaction::class);
        $interaction->interaction_id = self::INTERACTION_ID;

        return [[$builderEmail, $emailHistory, $interaction]];
    }

    /**
     * @return array[]
     */
    public function sendEmailDataProvider(): array
    {
        $builderEmail = new BuilderEmail([
            'id' => self::TEMPLATE_ID,
            'emailId' => self::PARSED_EMAIL_ID,
            'type' => 'template',
            'template' => self::SEND_TEMPLATE_HTML,
            'subject' => self::SEND_TEMPLATE_SUBJECT,
            'dealerId' => self::DEALER_ID,
            'userId' => self::NEW_DEALER_USER_ID,
            'salesPersonId' => self::SALES_PERSON_ID,
            'fromEmail' => self::SALES_PERSON_SMTP_EMAIL,
            'toEmail' => self::SEND_TEMPLATE_TO_EMAIL,
            'templateId' => self::TEMPLATE_ID,
            'leadId' => self::FIRST_LEAD_ID,
            'toName' => self::SALES_PERSON_FULL_NAME,
        ]);

        $parsedEmail = new ParsedEmail([
            'emailHistoryId' => self::PARSED_EMAIL_ID,
            'leadId' => self::FIRST_LEAD_ID,
            'to' => self::SEND_TEMPLATE_TO_EMAIL,
            'toName' => self::SALES_PERSON_FULL_NAME,
            'from' => self::SALES_PERSON_SMTP_EMAIL,
            'subject' => self::SEND_TEMPLATE_SUBJECT,
            'body' => self::SEND_TEMPLATE_HTML,
            'isHtml' => true,
        ]);

        $salesperson = $this->getEloquentMock(SalesPerson::class);
        $salesperson->id = self::SALES_PERSON_ID;
        $salesperson->smtp_email = self::SALES_PERSON_SMTP_EMAIL;
        $salesperson->smtp_password = self::SALES_PERSON_SMTP_PASSWORD;
        $salesperson->smtp_server = self::SALES_PERSON_SMTP_SERVER;
        $salesperson->smtp_port = self::SALES_PERSON_SMTP_PORT;
        $salesperson->smtp_security = self::SALES_PERSON_SMTP_SECURITY;
        $salesperson->smtp_auth = self::SALES_PERSON_SMTP_AUTH;

        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->access_token = self::ACCESS_TOKEN_ACCESS_TOKEN;

        $salesperson
            ->shouldReceive('getActiveTokenAttribute')
            ->andReturn($accessToken);

        $salesperson
            ->shouldReceive('getFullNameAttribute')
            ->andReturn(self::SALES_PERSON_FULL_NAME);

        $validateToken = new ValidateToken();
        $validateToken->setAccessToken($accessToken);

        return [[$builderEmail, $parsedEmail, $salesperson, $validateToken]];
    }

    /**
     * @return array[]
     */
    public function markSentDataProvider(): array
    {
        $builderEmail = new BuilderEmail([
            'id' => self::TEMPLATE_ID,
            'type' => 'template',
            'template' => self::SEND_TEMPLATE_HTML,
            'subject' => self::SEND_TEMPLATE_SUBJECT,
            'dealerId' => self::DEALER_ID,
            'userId' => self::NEW_DEALER_USER_ID,
            'salesPersonId' => self::SALES_PERSON_ID,
            'fromEmail' => self::SALES_PERSON_SMTP_EMAIL,
            'toEmail' => self::SEND_TEMPLATE_TO_EMAIL,
            'templateId' => self::TEMPLATE_ID,
            'leadId' => self::FIRST_LEAD_ID
        ]);

        $parsedEmail = new ParsedEmail([
            'emailHistoryId' => self::PARSED_EMAIL_ID,
            'leadId' => self::FIRST_LEAD_ID,
            'to' => self::SEND_TEMPLATE_TO_EMAIL,
            'toName' => self::SALES_PERSON_FULL_NAME,
            'from' => self::SALES_PERSON_SMTP_EMAIL,
            'subject' => self::SEND_TEMPLATE_SUBJECT,
            'body' => self::SEND_TEMPLATE_HTML,
            'isHtml' => true,
        ]);

        $blastSent = $this->getEloquentMock(BlastSent::class);
        $blastSent->lead_id = $builderEmail->leadId;

        $campaignSent = $this->getEloquentMock(CampaignSent::class);
        $campaignSent->lead_id = $builderEmail->leadId;

        return [[$builderEmail, $parsedEmail, $blastSent, $campaignSent]];
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
}
