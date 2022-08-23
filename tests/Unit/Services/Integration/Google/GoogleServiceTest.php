<?php

namespace Tests\Unit\Services\Integration\Google;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\LoginUrlToken;
use App\Services\Integration\Google\GoogleService;
use Google_Client;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Mockery\LegacyMockInterface;
use Tests\TestCase;
use Mockery;

/**
 * Test for App\Services\Integration\GoogleService
 *
 * Class GoogleServiceTest
 * @package Tests\Unit\Services\Integration
 *
 * @coversDefaultClass \App\Services\Integration\Google\GoogleService
 */
class GoogleServiceTest extends TestCase
{
    private const REDIRECT_URI = 'some_redirect_uri.com';
    private const SCOPES = ['some_scopes'];
    private const LOGIN_URL = 'some_login_url.com';

    private const CONFIG_OAUTH_GOOGLE_REDIRECT_URI = 'config_oauth_google_redirect_uri.com';
    private const CONFIG_OAUTH_GOOGLE_SCOPES = ['config', 'oauth_google_scopes'];
    private const CONFIG_OAUTH_SYSTEM_REDIRECT_URI = 'config_oauth_google_redirect_uri.com';
    private const CONFIG_OAUTH_SYSTEM_SCOPES = ['config', 'oauth_google_scopes'];

    /**
     * @var GoogleService|LegacyMockInterface
     */
    private $googleService;

    public function setUp(): void
    {
        parent::setUp();

        $this->googleService = Mockery::mock(GoogleService::class);

        Config::set('oauth.google.redirectUri', self::CONFIG_OAUTH_GOOGLE_REDIRECT_URI);
        Config::set('oauth.google.scopes', implode(' ',self::CONFIG_OAUTH_GOOGLE_SCOPES));
        Config::set('oauth.system.redirectUri', self::CONFIG_OAUTH_SYSTEM_REDIRECT_URI);
        Config::set('oauth.system.scopes', implode(' ',self::CONFIG_OAUTH_SYSTEM_SCOPES));
    }

    /**
     * @group CRM
     * @covers ::getClient
     *
     * @dataProvider loginDataProvider
     *
     * @param string $redirectUri
     * @param string $loginUrl
     * @param array $scopes
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testGetClient(string $redirectUri, string $loginUrl, array $scopes, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setRedirectUri')
            ->with($redirectUri)
            ->once();

        $googleClient
            ->shouldReceive('createAuthUrl')
            ->with($scopes)
            ->once()
            ->andReturn($loginUrl);

        $this->googleService
            ->shouldReceive('login')
            ->passthru();

        $result = $this->googleService->login($redirectUri, $scopes);

        $this->assertInstanceOf(LoginUrlToken::class, $result);
        $this->assertEquals($loginUrl, $result->loginUrl);
    }

    /**
     * @group CRM
     * @covers ::login
     *
     * @dataProvider loginDataProvider
     *
     * @param string $redirectUri
     * @param string $loginUrl
     * @param array $scopes
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testLogin(string $redirectUri, string $loginUrl, array $scopes, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setRedirectUri')
            ->with($redirectUri)
            ->once();

        $googleClient
            ->shouldReceive('createAuthUrl')
            ->with($scopes)
            ->once()
            ->andReturn($loginUrl);

        $this->googleService
            ->shouldReceive('login')
            ->passthru();

        $result = $this->googleService->login($redirectUri, $scopes);

        $this->assertInstanceOf(LoginUrlToken::class, $result);
        $this->assertEquals($loginUrl, $result->loginUrl);
    }

    /**
     * @group CRM
     * @covers ::login
     *
     * @dataProvider loginDataProvider
     *
     * @param string $redirectUri
     * @param string $loginUrl
     * @param array $scopes
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testLoginWithoutParams(string $redirectUri, string $loginUrl, array $scopes, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setRedirectUri')
            ->with(self::CONFIG_OAUTH_GOOGLE_REDIRECT_URI)
            ->once();

        $googleClient
            ->shouldReceive('createAuthUrl')
            ->with(self::CONFIG_OAUTH_GOOGLE_SCOPES)
            ->once()
            ->andReturn($loginUrl);

        $this->googleService
            ->shouldReceive('login')
            ->passthru();

        $result = $this->googleService->login();

        $this->assertInstanceOf(LoginUrlToken::class, $result);
        $this->assertEquals($loginUrl, $result->loginUrl);
    }

    /**
     * @group CRM
     * @covers ::login
     *
     * @dataProvider loginDataProvider
     *
     * @param string $redirectUri
     * @param string $loginUrl
     * @param array $scopes
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testLoginWithSystemType(string $redirectUri, string $loginUrl, array $scopes, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setRedirectUri')
            ->with(self::CONFIG_OAUTH_SYSTEM_REDIRECT_URI)
            ->once();

        $googleClient
            ->shouldReceive('createAuthUrl')
            ->with(self::CONFIG_OAUTH_SYSTEM_SCOPES)
            ->once()
            ->andReturn($loginUrl);

        $this->googleService
            ->shouldReceive('setKey')
            ->passthru();

        $this->googleService
            ->shouldReceive('login')
            ->passthru();

        $this->googleService->setKey('system');

        $result = $this->googleService->login();

        $this->assertInstanceOf(LoginUrlToken::class, $result);
        $this->assertEquals($loginUrl, $result->loginUrl);
    }

    /**
     * @group CRM
     * @covers ::refresh
     *
     * @throws BindingResolutionException
     */
    public function atestRefresh()
    {
        // Get Test Token
        $accessToken = factory(AccessToken::class)->make();

        /** @var GoogleService $service */
        $service = $this->app->make(GoogleService::class);

        // Validate Show Catalog Result
        $result = $service->refresh($accessToken);

        // Assert New Token is Set
        $this->assertTrue(!empty($result));
    }

    /**
     * @group CRM
     * @covers ::validate
     *
     * @throws BindingResolutionException
     */
    public function atestValidate()
    {
        // Get Test Token
        $accessToken = factory(AccessToken::class)->make();

        /** @var GoogleService $service */
        $service = $this->app->make(GoogleService::class);

        // Validate Show Catalog Result
        $result = $service->validate($accessToken);

        // Assert True
        $this->assertTrue($result['is_valid']);

        // Assert False
        $this->assertFalse($result['is_expired']);
    }

    /**
     * @return array[]
     */
    public function loginDataProvider(): array
    {
        $redirectUri = self::REDIRECT_URI;
        $loginUrl = self::LOGIN_URL;
        $scopes = self::SCOPES;
        $googleClient = Mockery::mock(Google_Client::class);

        return [[$redirectUri, $loginUrl, $scopes, $googleClient]];
    }
}
