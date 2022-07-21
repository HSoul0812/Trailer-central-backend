<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Exceptions\CRM\Leads\Import\MissingAdfEmailAccessTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Models\System\Email;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\System\EmailRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Leads\Import\ADFService;
use App\Services\CRM\Leads\Import\HtmlService;
use App\Services\CRM\Leads\Import\ImportService;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Leads\Import\ImportService
 *
 * Class ImportServiceTest
 * @package Tests\Unit\Services\CRM\Leads
 *
 * @coversDefaultClass \App\Services\CRM\Leads\Import\ImportService
 */
class ImportServiceTest extends TestCase
{
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

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('emailRepository', EmailRepositoryInterface::class);
        $this->instanceMock('googleService', GoogleServiceInterface::class);
        $this->instanceMock('tokenRepository', TokenRepositoryInterface::class);
        $this->instanceMock('userRepository', UserRepositoryInterface::class);
        $this->instanceMock('gmailService', GmailServiceInterface::class);
        $this->instanceMock('adfService', ADFService::class);
        $this->instanceMock('htmlService', HtmlService::class);
    }

    /**
     * @group CRM
     * @covers ::import
     *
     * @dataProvider importParamsProvider
     */
    public function testImport($systemEmail, $accessToken)
    {
        $this->emailRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($systemEmail);

        /** @var ImportService $service */
        $service = $this->app->make(ImportService::class);

        $service->import();
    }

    /**
     * @group CRM
     * @covers ::import
     */
    public function testImportWithoutGoogleToken()
    {
        $systemEmail = $this->getEloquentMock(Email::class);
        $hasOne = Mockery::mock(HasOne::class);

        $systemEmail->shouldReceive('setRelation')->passthru();
        $systemEmail->shouldReceive('googleToken')->andReturn($hasOne);

        $hasOne->shouldReceive('getResults')->andReturn(null);

        $this->emailRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($systemEmail);

        $this->expectException(MissingAdfEmailAccessTokenException::class);

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
        $hasOne = Mockery::mock(HasOne::class);

        $systemEmail->shouldReceive('setRelation')->passthru();
        $systemEmail->shouldReceive('googleToken')->andReturn($hasOne);

        $hasOne->shouldReceive('getResults')->andReturn($accessToken);

        return [[$systemEmail, $accessToken]];
    }
}
