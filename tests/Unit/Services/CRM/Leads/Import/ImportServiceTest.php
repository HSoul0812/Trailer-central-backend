<?php

namespace Tests\Unit\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\MissingEmailAccessTokenException;
use App\Models\CRM\Leads\Lead;
use App\Models\Integration\Auth\AccessToken;
use App\Models\System\Email;
use App\Models\User\User;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\System\EmailRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\Import\ADFService;
use App\Services\CRM\Leads\Import\HtmlService;
use App\Services\CRM\Leads\Import\HtmlServices\BoatsCom;
use App\Services\CRM\Leads\Import\ImportService;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleService;
use App\Services\Integration\Google\GoogleServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery\LegacyMockInterface;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Leads\Import\ImportService
 *
 * Class ImportServiceTest
 * @package Tests\Unit\Services\CRM\Leads\Import
 *
 * @coversDefaultClass \App\Services\CRM\Leads\Import\ImportService
 */
class ImportServiceTest extends TestCase
{
    const VALIDATE_ACCESS_TOKEN = 'validate_access_token';
    const VALIDATE_ID_TOKEN = 'validate_id_token';
    const VALIDATE_EXPIRES_IN = 'expires_in';
    const ACCESS_TOKEN_ID = PHP_INT_MAX;
    const TO_EMAIL = 'some@email.com';

    /**
     * @var EmailRepositoryInterface|LegacyMockInterface
     */
    protected $emailRepository;

    /**
     * @var GoogleServiceInterface|LegacyMockInterface
     */
    protected $googleService;

    /**
     * @var TokenRepositoryInterface|LegacyMockInterface
     */
    protected $tokenRepository;

    /**
     * @var UserRepositoryInterface|LegacyMockInterface
     */
    protected $userRepository;

    /**
     * @var GmailServiceInterface|LegacyMockInterface
     */
    protected $gmailService;

    /**
     * @var LeadServiceInterface|LegacyMockInterface
     */
    protected $leadService;

    /**
     * @var ADFService|LegacyMockInterface
     */
    protected $adfService;

    /**
     * @var HtmlService|LegacyMockInterface
     */
    protected $htmlService;

    /**
     * @var BoatsCom|LegacyMockInterface
     */
    protected $boatsCom;

    /**
     * @var LoggerInterface|LegacyMockInterface
     */
    protected $logMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('emailRepository', EmailRepositoryInterface::class);
        $this->instanceMock('googleService', GoogleServiceInterface::class);
        $this->instanceMock('tokenRepository', TokenRepositoryInterface::class);
        $this->instanceMock('userRepository', UserRepositoryInterface::class);
        $this->instanceMock('gmailService', GmailServiceInterface::class);
        $this->instanceMock('leadService', LeadServiceInterface::class);
        $this->instanceMock('adfService', ADFService::class);
        $this->instanceMock('htmlService', HtmlService::class);
        $this->instanceMock('logMock', LoggerInterface::class);
        $this->instanceMock('boatsCom', BoatsCom::class);

        Config::set('adf.imports.gmail.move', true);
    }

    /**
     * @group CRM
     * @covers ::import
     *
     * @dataProvider importParamsProvider
     */
    public function testImport($systemEmail, $accessToken, $validateToken, $messages, $email)
    {
        $dealer = $this->getEloquentMock(User::class);
        $lead = $this->getEloquentMock(Lead::class);

        $adfLead = new ADFLead();
        $adfLead->setWebsiteId(PHP_INT_MAX - 1);
        $adfLead->setDealerId(PHP_INT_MAX - 2);
        $adfLead->setLocationId(PHP_INT_MAX - 3);
        $adfLead->setVehicleId(PHP_INT_MAX - 4);
        $adfLead->setLeadType('some_type');
        $adfLead->setFirstName('some_first_name');
        $adfLead->setLastName('some_last_name');
        $adfLead->setEmail('some_lead@email.com');
        $adfLead->setPhone(123456);
        $adfLead->setAddrStreet('some_street');
        $adfLead->setAddrCity('some_city');
        $adfLead->setAddrState('some_state');
        $adfLead->setAddrZip('some_zip');
        $adfLead->setComments('some_comments');
        $adfLead->setRequestDate((new Carbon())->toDateTimeString());
        $adfLead->setVendorProvider('some_provider');

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('info');

        $this->emailRepository
            ->shouldReceive('find')
            ->once()
            ->with(['email' => config('adf.imports.gmail.email')])
            ->andReturn($systemEmail);

        $this->googleService
            ->shouldReceive('setKey')
            ->with(GoogleService::AUTH_TYPE_SYSTEM)
            ->once();

        $this->googleService
            ->shouldReceive('validate')
            ->with($accessToken)
            ->once()
            ->andReturn($validateToken);

        $this->gmailService
            ->shouldReceive('messages')
            ->with($accessToken, config('adf.imports.gmail.inbox'))
            ->once()
            ->andReturn($messages);

        $this->gmailService
            ->shouldReceive('message')
            ->with($messages[0])
            ->once()
            ->andReturn($email);

        $this->htmlService
            ->shouldReceive('findSource')
            ->with($email)
            ->once()
            ->andReturn($this->boatsCom);

        $this->adfService
            ->shouldReceive('findSource')
            ->with($email)
            ->once()
            ->andReturn(null);

        $this->userRepository
            ->shouldReceive('get')
            ->with(['dealer_id' => str_replace('@' . config('adf.imports.gmail.domain'), '', $email->getToEmail())])
            ->once()
            ->andReturn($dealer);

        $this->boatsCom
            ->shouldReceive('getLead')
            ->with($dealer, $email)
            ->once()
            ->andReturn($adfLead);

        $this->leadService
            ->shouldReceive('create')
            ->with([
                'website_id' => $adfLead->getWebsiteId(),
                'dealer_id' => $adfLead->getDealerId(),
                'dealer_location_id' => $adfLead->getLocationId(),
                'inventory_id' => $adfLead->getVehicleId(),
                'lead_type' => $adfLead->getLeadType(),
                'referral' => 'adf',
                'title' => 'ADF Import',
                'first_name' => $adfLead->getFirstName(),
                'last_name' => $adfLead->getLastName(),
                'email_address' => $adfLead->getEmail(),
                'phone_number' => $adfLead->getPhone(),
                'preferred_contact' => $adfLead->getPreferredContact(),
                'address' => $adfLead->getAddrStreet(),
                'city' => $adfLead->getAddrCity(),
                'state' => $adfLead->getAddrState(),
                'zip' => $adfLead->getAddrZip(),
                'comments' => $adfLead->getComments(),
                'contact_email_sent' => $adfLead->getRequestDate(),
                'adf_email_sent' => $adfLead->getRequestDate(),
                'cdk_email_sent' => 1,
                'date_submitted' => $adfLead->getRequestDate(),
                'lead_source' => $adfLead->getVendorProvider()
            ])
            ->once()
            ->andReturn($lead);

        $this->gmailService
            ->shouldReceive('move')
            ->with($accessToken, $messages[0], [config('adf.imports.gmail.processed')], [config('adf.imports.gmail.inbox')])
            ->once();

        /** @var ImportService $service */
        $service = $this->app->make(ImportService::class);

        $result = $service->import();

        $this->assertEquals(1, $result);
    }

    /**
     * @group CRM
     * @covers ::import
     *
     * @dataProvider importParamsProvider
     */
    public function testImportWithException($systemEmail, $accessToken, $validateToken, $messages, $email)
    {
        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('info');

        $this->logMock
            ->shouldReceive('error');

        $this->emailRepository
            ->shouldReceive('find')
            ->once()
            ->with(['email' => config('adf.imports.gmail.email')])
            ->andReturn($systemEmail);

        $this->googleService
            ->shouldReceive('setKey')
            ->with(GoogleService::AUTH_TYPE_SYSTEM)
            ->once();

        $this->googleService
            ->shouldReceive('validate')
            ->with($accessToken)
            ->once()
            ->andReturn($validateToken);

        $this->gmailService
            ->shouldReceive('messages')
            ->with($accessToken, config('adf.imports.gmail.inbox'))
            ->once()
            ->andReturn($messages);

        $this->gmailService
            ->shouldReceive('message')
            ->with($messages[0])
            ->once()
            ->andReturn($email);

        $this->adfService
            ->shouldReceive('findSource')
            ->with($email)
            ->once()
            ->andThrow(new \Exception());

        $this->gmailService
            ->shouldReceive('move')
            ->never();

        $this->leadService
            ->shouldReceive('create')
            ->never();

        /** @var ImportService $service */
        $service = $this->app->make(ImportService::class);

        $result = $service->import();

        $this->assertEquals(0, $result);
    }

    /**
     * @group CRM
     * @covers ::import
     *
     * @dataProvider importParamsProvider
     */
    public function testImportWithInvalidDealerId($systemEmail, $accessToken, $validateToken, $messages, $email)
    {
        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('info');

        $this->logMock
            ->shouldReceive('error');

        $this->emailRepository
            ->shouldReceive('find')
            ->once()
            ->with(['email' => config('adf.imports.gmail.email')])
            ->andReturn($systemEmail);

        $this->googleService
            ->shouldReceive('setKey')
            ->with(GoogleService::AUTH_TYPE_SYSTEM)
            ->once();

        $this->googleService
            ->shouldReceive('validate')
            ->with($accessToken)
            ->once()
            ->andReturn($validateToken);

        $this->gmailService
            ->shouldReceive('messages')
            ->with($accessToken, config('adf.imports.gmail.inbox'))
            ->once()
            ->andReturn($messages);

        $this->gmailService
            ->shouldReceive('message')
            ->with($messages[0])
            ->once()
            ->andReturn($email);

        $this->htmlService
            ->shouldReceive('findSource')
            ->with($email)
            ->once()
            ->andReturn($this->boatsCom);

        $this->adfService
            ->shouldReceive('findSource')
            ->with($email)
            ->once()
            ->andReturn(null);

        $this->userRepository
            ->shouldReceive('get')
            ->with(['dealer_id' => str_replace('@' . config('adf.imports.gmail.domain'), '', $email->getToEmail())])
            ->once()
            ->andThrow(new \Exception());

        $this->gmailService
            ->shouldReceive('move')
            ->with($accessToken, $messages[0], [config('adf.imports.gmail.invalid')], [config('adf.imports.gmail.inbox')])
            ->once();

        $this->leadService
            ->shouldReceive('create')
            ->never();

        /** @var ImportService $service */
        $service = $this->app->make(ImportService::class);

        $result = $service->import();

        $this->assertEquals(0, $result);
    }

    /**
     * @group CRM
     * @covers ::import
     *
     * @dataProvider importParamsProvider
     */
    public function testImportWithWrongFormat($systemEmail, $accessToken, $validateToken, $messages, $email)
    {
        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('info');

        $this->logMock
            ->shouldReceive('error');

        $this->emailRepository
            ->shouldReceive('find')
            ->once()
            ->with(['email' => config('adf.imports.gmail.email')])
            ->andReturn($systemEmail);

        $this->googleService
            ->shouldReceive('setKey')
            ->with(GoogleService::AUTH_TYPE_SYSTEM)
            ->once();

        $this->googleService
            ->shouldReceive('validate')
            ->with($accessToken)
            ->once()
            ->andReturn($validateToken);

        $this->gmailService
            ->shouldReceive('messages')
            ->with($accessToken, config('adf.imports.gmail.inbox'))
            ->once()
            ->andReturn($messages);

        $this->gmailService
            ->shouldReceive('message')
            ->with($messages[0])
            ->once()
            ->andReturn($email);

        $this->htmlService
            ->shouldReceive('findSource')
            ->with($email)
            ->once()
            ->andReturn($this->boatsCom);

        $this->adfService
            ->shouldReceive('findSource')
            ->with($email)
            ->once()
            ->andReturn(null);

        $this->gmailService
            ->shouldReceive('move')
            ->with($accessToken, $messages[0], [config('adf.imports.gmail.invalid')], [config('adf.imports.gmail.inbox')])
            ->once();

        $this->leadService
            ->shouldReceive('create')
            ->never();

        /** @var ImportService $service */
        $service = $this->app->make(ImportService::class);

        $result = $service->import();

        $this->assertEquals(0, $result);
    }

    /**
     * @group CRM
     * @covers ::import
     *
     * @dataProvider importParamsProvider
     */
    public function testImportWithoutMessages($systemEmail, $accessToken, $validateToken, $messages, $email)
    {
        $messages = [];

        Log::shouldReceive('channel')
            ->once()
            ->andReturn($this->logMock);

        $this->logMock
            ->shouldReceive('info');

        $this->emailRepository
            ->shouldReceive('find')
            ->once()
            ->with(['email' => config('adf.imports.gmail.email')])
            ->andReturn($systemEmail);

        $this->googleService
            ->shouldReceive('setKey')
            ->with(GoogleService::AUTH_TYPE_SYSTEM)
            ->once();

        $this->googleService
            ->shouldReceive('validate')
            ->with($accessToken)
            ->once()
            ->andReturn($validateToken);

        $this->gmailService
            ->shouldReceive('messages')
            ->with($accessToken, config('adf.imports.gmail.inbox'))
            ->once()
            ->andReturn($messages);

        $this->leadService
            ->shouldReceive('create')
            ->never();

        /** @var ImportService $service */
        $service = $this->app->make(ImportService::class);

        $result = $service->import();

        $this->assertEquals(0, $result);
    }

    /**
     * @group CRM
     * @covers ::import
     */
    public function testImportWithoutGoogleToken()
    {
        $systemEmail = $this->getEloquentMock(Email::class);

        $this->initHasOneRelation($systemEmail, 'googleToken', null);

        $this->emailRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($systemEmail);

        $this->expectException(MissingEmailAccessTokenException::class);

        /** @var ImportService $service */
        $service = $this->app->make(ImportService::class);

        $service->import();
    }

    /**
     * @return object[][][]
     */
    public function importParamsProvider(): array
    {
        $systemEmail = $this->getEloquentMock(Email::class);
        $accessToken = $this->getEloquentMock(AccessToken::class);

        $this->initHasOneRelation($systemEmail, 'googleToken', $accessToken);

        $accessToken->id = self::ACCESS_TOKEN_ID;

        $validateToken = new ValidateToken();

        $messages = ['mailId1'];
        $email = new ParsedEmail();
        $email->setToEmail(self::TO_EMAIL);

        return [[$systemEmail, $accessToken, $validateToken, $messages, $email]];
    }
}
