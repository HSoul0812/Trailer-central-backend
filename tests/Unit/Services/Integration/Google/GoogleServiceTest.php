<?php

namespace Tests\Unit\Services\Integration\Google;

use App\Exceptions\Integration\Google\MissingGapiClientIdException;
use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\LoginUrlToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Google\GoogleService;
use Google_Client;
use Illuminate\Support\Facades\Config;
use Mockery\LegacyMockInterface;
use Psr\Log\LoggerInterface;
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

    private const CONFIG_OAUTH_GOOGLE_APP_ID = 'config_oauth_google_app_id';
    private const CONFIG_OAUTH_GOOGLE_APP_NAME = 'config_oauth_google_app_name';
    private const CONFIG_OAUTH_GOOGLE_APP_SECRET = 'config_oauth_google_app_secret';
    private const CONFIG_OAUTH_GOOGLE_REDIRECT_URI = 'config_oauth_google_redirect_uri.com';
    private const CONFIG_OAUTH_GOOGLE_SCOPES = ['config', 'oauth_google_scopes'];

    private const CONFIG_OAUTH_SYSTEM_APP_ID = 'config_oauth_system_app_id';
    private const CONFIG_OAUTH_SYSTEM_APP_NAME = 'config_oauth_system_app_name';
    private const CONFIG_OAUTH_SYSTEM_APP_SECRET = 'config_oauth_system_app_secret';
    private const CONFIG_OAUTH_SYSTEM_REDIRECT_URI = 'config_oauth_system_redirect_uri.com';
    private const CONFIG_OAUTH_SYSTEM_SCOPES = ['config', 'oauth_system_scopes'];

    private const CONFIG_ACCESS_TYPE = 'offline';

    private const ACCESS_TOKEN_ACCESS_TOKEN = 'access_token_access_token';
    private const ACCESS_TOKEN_REFRESH_TOKEN = 'access_token_refresh_token';
    private const ACCESS_TOKEN_ID_TOKEN = 'access_token_id_token';
    private const ACCESS_TOKEN_EXPIRES_IN = 123456;
    private const ACCESS_TOKEN_ISSUED_AT = '2022-01-01 00:00:00';
    private const ACCESS_TOKEN_SCOPE = 'access_token scope';

    private const GOOGLE_SERVICE_PAYLOAD = ['google_service_payload'];

    private const GOOGLE_REFRESH_TOKEN = 'some_google_refresh_token';

    private const SUCCESS_GOOGLE_VALIDATION_MESSAGE = 'Your Google Authorization has been validated successfully!';
    private const FAILED_GOOGLE_VALIDATION_MESSAGE = 'Your Google Authorization failed! Please try connecting again.';
    private const GOOGLE_VALIDATION_EXPIRED_MESSAGE = 'Your Google Authorization has expired! Please try connecting again.';

    /**
     * @var GoogleService|LegacyMockInterface
     */
    private $googleService;

    public function setUp(): void
    {
        parent::setUp();

        $this->googleService = Mockery::mock(GoogleService::class);
        $this->googleService->log = Mockery::mock(LoggerInterface::class);

        Config::set('oauth.google.app.id', self::CONFIG_OAUTH_GOOGLE_APP_ID);
        Config::set('oauth.google.app.name', self::CONFIG_OAUTH_GOOGLE_APP_NAME);
        Config::set('oauth.google.app.secret', self::CONFIG_OAUTH_GOOGLE_APP_SECRET);
        Config::set('oauth.google.redirectUri', self::CONFIG_OAUTH_GOOGLE_REDIRECT_URI);
        Config::set('oauth.google.scopes', implode(' ',self::CONFIG_OAUTH_GOOGLE_SCOPES));

        Config::set('oauth.system.app.id', self::CONFIG_OAUTH_SYSTEM_APP_ID);
        Config::set('oauth.system.app.name', self::CONFIG_OAUTH_SYSTEM_APP_NAME);
        Config::set('oauth.system.app.secret', self::CONFIG_OAUTH_SYSTEM_APP_SECRET);
        Config::set('oauth.system.redirectUri', self::CONFIG_OAUTH_SYSTEM_REDIRECT_URI);
        Config::set('oauth.system.scopes', implode(' ',self::CONFIG_OAUTH_SYSTEM_SCOPES));
    }

    /**
     * @group CRM
     * @covers ::getClient
     */
    public function testGetClient()
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->passthru();

        $result = $this->googleService->getClient();

        $this->assertInstanceOf(Google_Client::class, $result);

        $config = $this->getFromPrivateProperty($result, 'config');

        $this->assertSame(self::CONFIG_OAUTH_GOOGLE_APP_ID, $config['client_id']);
        $this->assertSame(self::CONFIG_OAUTH_GOOGLE_APP_NAME, $config['application_name']);
        $this->assertSame(self::CONFIG_OAUTH_GOOGLE_APP_SECRET, $config['client_secret']);
        $this->assertSame(self::CONFIG_ACCESS_TYPE, $config['access_type']);
        $this->assertTrue($config['include_granted_scopes']);
    }

    /**
     * @group CRM
     * @covers ::getClient
     */
    public function testGetClientWithSystemType()
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->passthru();

        $this->googleService
            ->shouldReceive('setKey')
            ->passthru();

        $this->googleService->setKey('system');

        $result = $this->googleService->getClient();

        $this->assertInstanceOf(Google_Client::class, $result);

        $config = $this->getFromPrivateProperty($result, 'config');

        $this->assertSame(self::CONFIG_OAUTH_SYSTEM_APP_ID, $config['client_id']);
        $this->assertSame(self::CONFIG_OAUTH_SYSTEM_APP_NAME, $config['application_name']);
        $this->assertSame(self::CONFIG_OAUTH_SYSTEM_APP_SECRET, $config['client_secret']);
        $this->assertSame(self::CONFIG_ACCESS_TYPE, $config['access_type']);
        $this->assertTrue($config['include_granted_scopes']);
    }

    /**
     * @group CRM
     * @covers ::getClient
     */
    public function testGetClientWithEmptyConfigAppId()
    {
        Config::set('oauth.google.app.id', null);

        $this->expectException(MissingGapiClientIdException::class);

        $this->googleService
            ->shouldReceive('getClient')
            ->passthru();

        $this->googleService->getClient();
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
     * @dataProvider validateDataProvider
     *
     * @param AccessToken|LegacyMockInterface $accessToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testRefresh(AccessToken $accessToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'id_token' => $accessToken->id_token,
                'expires_in' => $accessToken->expires_in,
                'created' => strtotime($accessToken->issued_at)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with($accessToken->scope)
            ->once();

        $googleClient
            ->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->with($accessToken->refresh_token)
            ->once()
            ->andReturn($newToken);

        $this->googleService
            ->shouldReceive('refresh')
            ->passthru();

        $result = $this->googleService->refresh($accessToken);

        $this->assertInstanceOf(EmailToken::class, $result);

        $this->assertSame($newToken['access_token'], $result->accessToken);
        $this->assertSame($newToken['refresh_token'], $result->refreshToken);
        $this->assertSame($newToken['id_token'], $result->idToken);
        $this->assertSame(explode(" ", $newToken['scope']), $result->scopes);
        $this->assertSame($newToken['issued_at'], $result->issuedAt);
        $this->assertSame($newToken['expires_in'], $result->expiresIn);
    }

    /**
     * @group CRM
     * @covers ::refresh
     *
     * @dataProvider validateDataProvider
     *
     * @param AccessToken|LegacyMockInterface $accessToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testRefreshWithoutNewToken(AccessToken $accessToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'id_token' => $accessToken->id_token,
                'expires_in' => $accessToken->expires_in,
                'created' => strtotime($accessToken->issued_at)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with($accessToken->scope)
            ->once();

        $googleClient
            ->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->with($accessToken->refresh_token)
            ->once()
            ->andReturn([]);

        $this->googleService
            ->shouldReceive('refresh')
            ->passthru();

        $result = $this->googleService->refresh($accessToken);

        $this->assertInstanceOf(EmailToken::class, $result);

        $this->assertNull($result->accessToken);
        $this->assertNull($result->refreshToken);
        $this->assertNull($result->idToken);
        $this->assertNull($result->scopes);
        $this->assertNull($result->issuedAt);
        $this->assertNull($result->expiresIn);
    }

    /**
     * @group CRM
     * @covers ::validate
     *
     * @dataProvider validateDataProvider
     *
     * @param AccessToken|LegacyMockInterface $accessToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidate(AccessToken $accessToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->twice()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'id_token' => $accessToken->id_token,
                'expires_in' => $accessToken->expires_in,
                'created' => strtotime($accessToken->issued_at)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with($accessToken->scope)
            ->once();

        $this->googleService->log
            ->shouldReceive('info');

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($accessToken->id_token)
            ->once()
            ->andReturn(self::GOOGLE_SERVICE_PAYLOAD);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->googleService
            ->shouldReceive('validate')
            ->passthru();

        $result = $this->googleService->validate($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertNull($result->newToken);
        $this->assertTrue($result->isValid);
        $this->assertFalse($result->isExpired);
        $this->assertEquals(self::SUCCESS_GOOGLE_VALIDATION_MESSAGE, $result->message);
    }

    /**
     * @group CRM
     * @covers ::validate
     *
     * @dataProvider validateDataProvider
     *
     * @param AccessToken|LegacyMockInterface $accessToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateWithNotValidIdToken(AccessToken $accessToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->times(3)
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'id_token' => $accessToken->id_token,
                'expires_in' => $accessToken->expires_in,
                'created' => strtotime($accessToken->issued_at)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with($accessToken->scope)
            ->once();

        $this->googleService->log
            ->shouldReceive('info');

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($accessToken->id_token)
            ->twice()
            ->andReturn(false);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $googleClient
            ->shouldReceive('getRefreshToken')
            ->withNoArgs()
            ->once()
            ->andReturn(self::GOOGLE_REFRESH_TOKEN);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->with(self::GOOGLE_REFRESH_TOKEN)
            ->once()
            ->andReturn($newToken);

        $this->googleService
            ->shouldReceive('validate')
            ->passthru();

        $result = $this->googleService->validate($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertFalse($result->isValid);
        $this->assertTrue($result->isExpired);
        $this->assertEquals(self::FAILED_GOOGLE_VALIDATION_MESSAGE, $result->message);

        $this->assertInstanceOf(EmailToken::class, $result->newToken);

        $this->assertSame($newToken['access_token'], $result->newToken->accessToken);
        $this->assertSame($newToken['refresh_token'], $result->newToken->refreshToken);
        $this->assertSame($newToken['id_token'], $result->newToken->idToken);
        $this->assertSame(explode(" ", $newToken['scope']), $result->newToken->scopes);
        $this->assertSame($newToken['issued_at'], $result->newToken->issuedAt);
        $this->assertSame($newToken['expires_in'], $result->newToken->expiresIn);
    }

    /**
     * @group CRM
     * @covers ::validate
     *
     * @dataProvider validateDataProvider
     *
     * @param AccessToken|LegacyMockInterface $accessToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateWithExpiredToken(AccessToken $accessToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->times(3)
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'id_token' => $accessToken->id_token,
                'expires_in' => $accessToken->expires_in,
                'created' => strtotime($accessToken->issued_at)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with($accessToken->scope)
            ->once();

        $this->googleService->log
            ->shouldReceive('info');

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($accessToken->id_token)
            ->twice()
            ->andReturn(self::GOOGLE_SERVICE_PAYLOAD);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $googleClient
            ->shouldReceive('getRefreshToken')
            ->withNoArgs()
            ->once()
            ->andReturn(self::GOOGLE_REFRESH_TOKEN);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->with(self::GOOGLE_REFRESH_TOKEN)
            ->once()
            ->andReturn($newToken);

        $this->googleService
            ->shouldReceive('validate')
            ->passthru();

        $result = $this->googleService->validate($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertTrue($result->isValid);
        $this->assertFalse($result->isExpired);
        $this->assertEquals(self::SUCCESS_GOOGLE_VALIDATION_MESSAGE, $result->message);

        $this->assertInstanceOf(EmailToken::class, $result->newToken);

        $this->assertSame($newToken['access_token'], $result->newToken->accessToken);
        $this->assertSame($newToken['refresh_token'], $result->newToken->refreshToken);
        $this->assertSame($newToken['id_token'], $result->newToken->idToken);
        $this->assertSame(explode(" ", $newToken['scope']), $result->newToken->scopes);
        $this->assertSame($newToken['issued_at'], $result->newToken->issuedAt);
        $this->assertSame($newToken['expires_in'], $result->newToken->expiresIn);
    }

    /**
     * @group CRM
     * @covers ::validate
     *
     * @dataProvider validateDataProvider
     *
     * @param AccessToken|LegacyMockInterface $accessToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateWithExpiredTokenWithoutAccessToken(AccessToken $accessToken, array $newToken, Google_Client $googleClient)
    {
        unset($newToken['access_token']);

        $this->googleService
            ->shouldReceive('getClient')
            ->twice()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'id_token' => $accessToken->id_token,
                'expires_in' => $accessToken->expires_in,
                'created' => strtotime($accessToken->issued_at)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with($accessToken->scope)
            ->once();

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($accessToken->id_token)
            ->once()
            ->andReturn(self::GOOGLE_SERVICE_PAYLOAD);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->googleService->log
            ->shouldReceive('info')
            ->once()
            ->withAnyArgs();

        $googleClient
            ->shouldReceive('getRefreshToken')
            ->withNoArgs()
            ->once()
            ->andReturn(self::GOOGLE_REFRESH_TOKEN);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->with(self::GOOGLE_REFRESH_TOKEN)
            ->once()
            ->andReturn($newToken);

        $this->googleService->log
            ->shouldReceive('error')
            ->withAnyArgs();

        $this->googleService
            ->shouldReceive('validate')
            ->passthru();

        $result = $this->googleService->validate($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertTrue($result->isValid);
        $this->assertTrue($result->isExpired);
        $this->assertEquals(self::GOOGLE_VALIDATION_EXPIRED_MESSAGE, $result->message);

        $this->assertInstanceOf(EmailToken::class, $result->newToken);

        $this->assertNull($result->newToken->accessToken);
        $this->assertNull($result->newToken->refreshToken);
        $this->assertNull($result->newToken->idToken);
        $this->assertNull($result->newToken->scopes);
        $this->assertNull($result->newToken->issuedAt);
        $this->assertNull($result->newToken->expiresIn);
    }

    /**
     * @group CRM
     * @covers ::validate
     *
     * @dataProvider validateDataProvider
     *
     * @param AccessToken|LegacyMockInterface $accessToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateWithException(AccessToken $accessToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->twice()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'id_token' => $accessToken->id_token,
                'expires_in' => $accessToken->expires_in,
                'created' => strtotime($accessToken->issued_at)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with($accessToken->scope)
            ->once();

        $this->googleService->log
            ->shouldReceive('info')
            ->withAnyArgs();

        $this->googleService->log
            ->shouldReceive('error')
            ->withAnyArgs();

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($accessToken->id_token)
            ->once()
            ->andReturn(self::GOOGLE_SERVICE_PAYLOAD);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andThrow(\Exception::class);

        $this->googleService
            ->shouldReceive('validate')
            ->passthru();

        $result = $this->googleService->validate($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertNull($result->newToken);
        $this->assertFalse($result->isValid);
        $this->assertTrue($result->isExpired);
        $this->assertEquals(self::FAILED_GOOGLE_VALIDATION_MESSAGE, $result->message);
    }

    /**
     * @group CRM
     * @covers ::validateCustom
     *
     * @dataProvider validateCustomDataProvider
     *
     * @param CommonToken|LegacyMockInterface $commonToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateCustom(CommonToken $commonToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->twice()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $commonToken->accessToken,
                'refresh_token' => $commonToken->refreshToken,
                'id_token' => $commonToken->idToken,
                'expires_in' => $commonToken->expiresIn,
                'created' => strtotime($commonToken->issuedAt)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with(implode(' ', $commonToken->scopes))
            ->once();

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($commonToken->idToken)
            ->once()
            ->andReturn(self::GOOGLE_SERVICE_PAYLOAD);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->googleService->log
            ->shouldReceive('info')
            ->withAnyArgs();

        $this->googleService
            ->shouldReceive('validateCustom')
            ->passthru();

        $result = $this->googleService->validateCustom($commonToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertNull($result->newToken);
        $this->assertTrue($result->isValid);
        $this->assertFalse($result->isExpired);
        $this->assertEquals(self::SUCCESS_GOOGLE_VALIDATION_MESSAGE, $result->message);
    }

    /**
     * @group CRM
     * @covers ::validateCustom
     *
     * @dataProvider validateCustomDataProvider
     *
     * @param CommonToken|LegacyMockInterface $commonToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateCustomWithNotValidIdToken(CommonToken $commonToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->times(3)
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $commonToken->accessToken,
                'refresh_token' => $commonToken->refreshToken,
                'id_token' => $commonToken->idToken,
                'expires_in' => $commonToken->expiresIn,
                'created' => strtotime($commonToken->issuedAt)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with(implode(' ', $commonToken->scopes))
            ->once();

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($commonToken->idToken)
            ->twice()
            ->andReturn(false);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->googleService->log
            ->shouldReceive('info')
            ->twice()
            ->withAnyArgs();

        $googleClient
            ->shouldReceive('getRefreshToken')
            ->withNoArgs()
            ->once()
            ->andReturn(self::GOOGLE_REFRESH_TOKEN);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->with(self::GOOGLE_REFRESH_TOKEN)
            ->once()
            ->andReturn($newToken);

        $this->googleService
            ->shouldReceive('validateCustom')
            ->passthru();

        $result = $this->googleService->validateCustom($commonToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertFalse($result->isValid);
        $this->assertTrue($result->isExpired);
        $this->assertEquals(self::FAILED_GOOGLE_VALIDATION_MESSAGE, $result->message);
        $this->assertInstanceOf(EmailToken::class, $result->newToken);

        $this->assertSame($newToken['access_token'], $result->newToken->accessToken);
        $this->assertSame($newToken['refresh_token'], $result->newToken->refreshToken);
        $this->assertSame($newToken['id_token'], $result->newToken->idToken);
        $this->assertSame(explode(" ", $newToken['scope']), $result->newToken->scopes);
        $this->assertSame($newToken['issued_at'], $result->newToken->issuedAt);
        $this->assertSame($newToken['expires_in'], $result->newToken->expiresIn);
    }

    /**
     * @group CRM
     * @covers ::validateCustom
     *
     * @dataProvider validateCustomDataProvider
     *
     * @param CommonToken|LegacyMockInterface $commonToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateCustomWithExpiredToken(CommonToken $commonToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->times(3)
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $commonToken->accessToken,
                'refresh_token' => $commonToken->refreshToken,
                'id_token' => $commonToken->idToken,
                'expires_in' => $commonToken->expiresIn,
                'created' => strtotime($commonToken->issuedAt)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with(implode(' ', $commonToken->scopes))
            ->once();

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($commonToken->idToken)
            ->twice()
            ->andReturn(self::GOOGLE_SERVICE_PAYLOAD);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->googleService->log
            ->shouldReceive('info')
            ->twice()
            ->withAnyArgs();

        $googleClient
            ->shouldReceive('getRefreshToken')
            ->withNoArgs()
            ->once()
            ->andReturn(self::GOOGLE_REFRESH_TOKEN);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->with(self::GOOGLE_REFRESH_TOKEN)
            ->once()
            ->andReturn($newToken);

        $this->googleService
            ->shouldReceive('validateCustom')
            ->passthru();

        $result = $this->googleService->validateCustom($commonToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertTrue($result->isValid);
        $this->assertFalse($result->isExpired);
        $this->assertEquals(self::SUCCESS_GOOGLE_VALIDATION_MESSAGE, $result->message);

        $this->assertInstanceOf(EmailToken::class, $result->newToken);

        $this->assertSame($newToken['access_token'], $result->newToken->accessToken);
        $this->assertSame($newToken['refresh_token'], $result->newToken->refreshToken);
        $this->assertSame($newToken['id_token'], $result->newToken->idToken);
        $this->assertSame(explode(" ", $newToken['scope']), $result->newToken->scopes);
        $this->assertSame($newToken['issued_at'], $result->newToken->issuedAt);
        $this->assertSame($newToken['expires_in'], $result->newToken->expiresIn);
    }

    /**
     * @group CRM
     * @covers ::validateCustom
     *
     * @dataProvider validateCustomDataProvider
     *
     * @param CommonToken|LegacyMockInterface $commonToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateCustomWithExpiredTokenWithoutAccessToken(
        CommonToken   $commonToken,
        array         $newToken,
        Google_Client $googleClient
    ) {
        unset($newToken['access_token']);

        $this->googleService
            ->shouldReceive('getClient')
            ->twice()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $commonToken->accessToken,
                'refresh_token' => $commonToken->refreshToken,
                'id_token' => $commonToken->idToken,
                'expires_in' => $commonToken->expiresIn,
                'created' => strtotime($commonToken->issuedAt)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with(implode(' ', $commonToken->scopes))
            ->once();

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($commonToken->idToken)
            ->once()
            ->andReturn(self::GOOGLE_SERVICE_PAYLOAD);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->googleService->log
            ->shouldReceive('info')
            ->once()
            ->withAnyArgs();

        $googleClient
            ->shouldReceive('getRefreshToken')
            ->withNoArgs()
            ->once()
            ->andReturn(self::GOOGLE_REFRESH_TOKEN);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->with(self::GOOGLE_REFRESH_TOKEN)
            ->once()
            ->andReturn($newToken);

        $this->googleService
            ->shouldReceive('validateCustom')
            ->passthru();

        $this->googleService->log
            ->shouldReceive('error')
            ->withAnyArgs();

        $result = $this->googleService->validateCustom($commonToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertTrue($result->isValid);
        $this->assertTrue($result->isExpired);
        $this->assertEquals(self::GOOGLE_VALIDATION_EXPIRED_MESSAGE, $result->message);

        $this->assertInstanceOf(EmailToken::class, $result->newToken);

        $this->assertNull($result->newToken->accessToken);
        $this->assertNull($result->newToken->refreshToken);
        $this->assertNull($result->newToken->idToken);
        $this->assertNull($result->newToken->scopes);
        $this->assertNull($result->newToken->issuedAt);
        $this->assertNull($result->newToken->expiresIn);
    }

    /**
     * @group CRM
     * @covers ::validateCustom
     *
     * @dataProvider validateCustomDataProvider
     *
     * @param CommonToken|LegacyMockInterface $commonToken
     * @param array $newToken
     * @param Google_Client|LegacyMockInterface $googleClient
     */
    public function testValidateCustomWithException(CommonToken $commonToken, array $newToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->twice()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setAccessToken')
            ->with([
                'access_token' => $commonToken->accessToken,
                'refresh_token' => $commonToken->refreshToken,
                'id_token' => $commonToken->idToken,
                'expires_in' => $commonToken->expiresIn,
                'created' => strtotime($commonToken->issuedAt)
            ])
            ->once();

        $googleClient
            ->shouldReceive('setScopes')
            ->with(implode(' ', $commonToken->scopes))
            ->once();

        $this->googleService->log
            ->shouldReceive('error')
            ->withAnyArgs();

        $googleClient
            ->shouldReceive('verifyIdToken')
            ->with($commonToken->idToken)
            ->once()
            ->andReturn(self::GOOGLE_SERVICE_PAYLOAD);

        $googleClient
            ->shouldReceive('isAccessTokenExpired')
            ->withNoArgs()
            ->once()
            ->andThrow(\Exception::class);

        $this->googleService
            ->shouldReceive('validateCustom')
            ->passthru();

        $result = $this->googleService->validateCustom($commonToken);

        $this->assertInstanceOf(ValidateToken::class, $result);

        $this->assertNull($result->newToken);
        $this->assertFalse($result->isValid);
        $this->assertTrue($result->isExpired);
        $this->assertEquals(self::FAILED_GOOGLE_VALIDATION_MESSAGE, $result->message);
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

    /**
     * @return array[]
     */
    public function validateDataProvider(): array
    {
        $accessToken = $this->getEloquentMock(AccessToken::class);

        $accessToken
            ->shouldReceive('getScopeAttribute')
            ->andReturn(self::ACCESS_TOKEN_SCOPE);

        $accessToken->access_token = self::ACCESS_TOKEN_ACCESS_TOKEN;
        $accessToken->refresh_token = self::ACCESS_TOKEN_REFRESH_TOKEN;
        $accessToken->id_token = self::ACCESS_TOKEN_ID_TOKEN;
        $accessToken->expires_in = self::ACCESS_TOKEN_EXPIRES_IN;
        $accessToken->issued_at = self::ACCESS_TOKEN_ISSUED_AT;

        $newToken = [
            'access_token' => self::ACCESS_TOKEN_ACCESS_TOKEN,
            'refresh_token' => self::ACCESS_TOKEN_REFRESH_TOKEN,
            'id_token' => self::ACCESS_TOKEN_ID_TOKEN,
            'scope' => self::ACCESS_TOKEN_SCOPE,
            'issued_at' => self::ACCESS_TOKEN_ISSUED_AT,
            'expires_in' => self::ACCESS_TOKEN_EXPIRES_IN,
        ];

        $googleClient = Mockery::mock(Google_Client::class);

        return [[$accessToken, $newToken, $googleClient]];
    }

    /**
     * @return array[]
     */
    public function validateCustomDataProvider(): array
    {
        $commonToken = new CommonToken([
            'access_token' => self::ACCESS_TOKEN_ACCESS_TOKEN,
            'refresh_token' => self::ACCESS_TOKEN_REFRESH_TOKEN,
            'id_token' => self::ACCESS_TOKEN_ID_TOKEN,
            'expires_in' => self::ACCESS_TOKEN_EXPIRES_IN,
            'issued_at' => self::ACCESS_TOKEN_ISSUED_AT,
            'scopes' => explode(' ',self::ACCESS_TOKEN_SCOPE),
        ]);

        $newToken = [
            'access_token' => self::ACCESS_TOKEN_ACCESS_TOKEN,
            'refresh_token' => self::ACCESS_TOKEN_REFRESH_TOKEN,
            'id_token' => self::ACCESS_TOKEN_ID_TOKEN,
            'scope' => self::ACCESS_TOKEN_SCOPE,
            'issued_at' => self::ACCESS_TOKEN_ISSUED_AT,
            'expires_in' => self::ACCESS_TOKEN_EXPIRES_IN,
        ];

        $googleClient = Mockery::mock(Google_Client::class);

        return [[$commonToken, $newToken, $googleClient]];
    }
}
