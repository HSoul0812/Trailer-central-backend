<?php

namespace Tests\Unit\Services\Integration;

use App\Exceptions\Integration\Auth\InvalidAuthCodeTokenTypeException;
use App\Exceptions\Integration\Auth\InvalidAuthLoginTokenTypeException;
use App\Exceptions\PropertyDoesNotExists;
use App\Http\Requests\Integration\Auth\AuthorizeTokenRequest;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\AuthService;
use App\Services\Integration\Common\DTOs\AuthLoginPayload;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\LoginUrlToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Scope;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Test for App\Services\Integration\AuthService
 *
 * Class AuthServiceTest
 * @package Tests\Unit\Services\Integration
 *
 * @coversDefaultClass \App\Services\Integration\AuthService
 */
class AuthServiceTest extends TestCase
{
    private const TOKEN_ID = PHP_INT_MAX;
    private const TOKEN_TYPE = 'google';
    private const TOKEN_TYPE_OFFICE = 'office365';
    private const TOKEN_RELATION_TYPE = 'sales_person';

    private const AUTH_LOGIN_PAYLOAD_REDIRECT_URI = 'some_uri.com';
    private const AUTH_LOGIN_PAYLOAD_RELATION_ID = 123456;
    private const AUTH_LOGIN_PAYLOAD_SCOPES= ['scopes'];

    private const LOGIN_URL_TOKEN_LOGIN_URL = 'some_url.com';
    private const LOGIN_URL_TOKEN_AUTH_STATE = false;

    private const TOKEN_CODE = 'some_code';

    private const EMAIL_TOKEN_FIRST_NAME = 'first_name';
    private const EMAIL_TOKEN_LAST_NAME = 'last_name';
    private const EMAIL_TOKEN_EMAIL_ADDRESS = 'some@email.com';

    private const STATE_TOKEN_ID = PHP_INT_MAX - 1;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|TokenRepositoryInterface
     */
    private $tokenRepositoryMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|GoogleServiceInterface
     */
    private $googleServiceMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|GmailServiceInterface
     */
    private $gmailServiceMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|OfficeServiceInterface
     */
    private $officeServiceMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|Manager
     */
    private $fractal;

    public function setUp(): void
    {
        parent::setUp();

        $this->googleServiceMock = Mockery::mock(GoogleServiceInterface::class);
        $this->app->instance(GoogleServiceInterface::class, $this->googleServiceMock);

        $this->gmailServiceMock = Mockery::mock(GmailServiceInterface::class);
        $this->app->instance(GmailServiceInterface::class, $this->gmailServiceMock);

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);

        $this->officeServiceMock = Mockery::mock(OfficeServiceInterface::class);
        $this->app->instance(OfficeServiceInterface::class, $this->officeServiceMock);

        $this->fractal = Mockery::mock(Manager::class);
        $this->app->instance(Manager::class, $this->fractal);

        $this->fractal
            ->shouldReceive('setSerializer')
            ->passthru();
    }

    /**
     * @group CRM
     * @covers ::index
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testIndex(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $indexRequestParams = [
            'token_type' => $accessToken->token_type,
            'relation_type' => $accessToken->relation_type,
            'relation_id' => $accessToken->id
        ];

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->tokenRepositoryMock
            ->shouldReceive('getRelation')
            ->once()
            ->with($indexRequestParams)
            ->andReturn($accessToken);

        $this->responseWithGoogleTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->index($indexRequestParams);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::index
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testIndexWithOfficeTokenType(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $indexRequestParams = [
            'token_type' => $accessToken->token_type,
            'relation_type' => $accessToken->relation_type,
            'relation_id' => $accessToken->id
        ];

        $accessToken->token_type = self::TOKEN_TYPE_OFFICE;

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->tokenRepositoryMock
            ->shouldReceive('getRelation')
            ->once()
            ->with($indexRequestParams)
            ->andReturn($accessToken);

        $this->responseWithOfficeTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->index($indexRequestParams);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::show
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testShow(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->tokenRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $accessToken->id])
            ->andReturn($accessToken);

        $this->responseWithGoogleTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->show($accessToken->id);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::show
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testShowWithOfficeTokenType(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $accessToken->token_type = self::TOKEN_TYPE_OFFICE;

        $this->tokenRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $accessToken->id])
            ->andReturn($accessToken);

        $this->responseWithOfficeTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->show($accessToken->id);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::create
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testCreate(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $indexRequestParams = [
            'token_type' => $accessToken->token_type,
            'relation_type' => $accessToken->relation_type,
            'relation_id' => $accessToken->id
        ];

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($indexRequestParams)
            ->andReturn($accessToken);

        $this->responseWithGoogleTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->create($indexRequestParams);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::create
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testCreateWithOfficeTokenType(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $indexRequestParams = [
            'token_type' => $accessToken->token_type,
            'relation_type' => $accessToken->relation_type,
            'relation_id' => $accessToken->id
        ];

        $accessToken->token_type = self::TOKEN_TYPE_OFFICE;

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($indexRequestParams)
            ->andReturn($accessToken);

        $this->responseWithOfficeTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->create($indexRequestParams);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::update
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testUpdate(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $indexRequestParams = [
            'token_type' => $accessToken->token_type,
            'relation_type' => $accessToken->relation_type,
            'relation_id' => $accessToken->id
        ];

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->tokenRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($indexRequestParams)
            ->andReturn($accessToken);

        $this->responseWithGoogleTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->update($indexRequestParams);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::update
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testUpdateWithOfficeTokenType(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $indexRequestParams = [
            'token_type' => $accessToken->token_type,
            'relation_type' => $accessToken->relation_type,
            'relation_id' => $accessToken->id
        ];

        $accessToken->token_type = self::TOKEN_TYPE_OFFICE;

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->tokenRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($indexRequestParams)
            ->andReturn($accessToken);

        $this->responseWithOfficeTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->update($indexRequestParams);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::validate
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testValidate(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->googleServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validateToken);

        $result = $service->validate($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);
        $this->assertEquals($validateToken, $result);
    }

    /**
     * @group CRM
     * @covers ::validate
     * @dataProvider authServiceDataProvider
     *
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testValidateWithOfficeTokenType(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $accessToken->token_type = self::TOKEN_TYPE_OFFICE;

        $this->officeServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validateToken);

        $result = $service->validate($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);
        $this->assertEquals($validateToken, $result);
    }

    /**
     * @group CRM
     * @covers ::validate
     * @dataProvider authServiceDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param LegacyMockInterface|MockInterface|ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @throws BindingResolutionException
     */
    public function testValidateWithNewToken(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $emailToken = Mockery::mock(EmailToken::class);
        $newAccessToken = $this->getEloquentMock(AccessToken::class);

        $validateToken->newToken = $emailToken;

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->googleServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validateToken);

        $emailToken
            ->shouldReceive('exists')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $this->tokenRepositoryMock
            ->shouldReceive('refresh')
            ->once()
            ->with($accessToken->id, $emailToken)
            ->andReturn($newAccessToken);

        $validateToken
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($newAccessToken);

        Log::shouldReceive('info');

        $result = $service->validate($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);
        $this->assertEquals($validateToken, $result);
    }

    /**
     * @group CRM
     * @covers ::validateCustom
     * @dataProvider validateCustomDataProvider
     *
     * @param CommonToken $accessToken
     * @param ValidateToken $validateToken
     * @throws BindingResolutionException
     */
    public function testValidateCustom(CommonToken $accessToken, ValidateToken $validateToken)
    {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->googleServiceMock
            ->shouldReceive('validateCustom')
            ->once()
            ->with($accessToken)
            ->andReturn($validateToken);

        $result = $service->validateCustom($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);
        $this->assertEquals($validateToken, $result);
    }

    /**
     * @group CRM
     * @covers ::validateCustom
     * @dataProvider validateCustomDataProvider
     *
     * @param CommonToken $accessToken
     * @param ValidateToken $validateToken
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function testValidateCustomWithOfficeTokenType(CommonToken $accessToken, ValidateToken $validateToken)
    {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->setToPrivateProperty($accessToken, 'tokenType', self::TOKEN_TYPE_OFFICE);

        $this->officeServiceMock
            ->shouldReceive('validateCustom')
            ->once()
            ->with($accessToken)
            ->andReturn($validateToken);

        $result = $service->validateCustom($accessToken);

        $this->assertInstanceOf(ValidateToken::class, $result);
        $this->assertEquals($validateToken, $result);
    }

    /**
     * @group CRM
     * @covers ::login
     * @dataProvider loginDataProvider
     *
     * @param AuthLoginPayload $authLoginPayload
     * @param LoginUrlToken $loginUrlToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $data
     * @throws BindingResolutionException
     * @throws InvalidAuthLoginTokenTypeException
     */
    public function testLogin(
        AuthLoginPayload $authLoginPayload,
        LoginUrlToken $loginUrlToken,
        Scope $fractalScope,
        array $data
    ) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->googleServiceMock
            ->shouldReceive('login')
            ->once()
            ->with($authLoginPayload->redirectUri, $authLoginPayload->scopes)
            ->andReturn($loginUrlToken);

        $this->fractal
            ->shouldReceive('createData')
            ->once()
            ->andReturn($fractalScope);

        $fractalScope
            ->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $result = $service->login($authLoginPayload);

        $this->assertEquals($data, $result);
    }

    /**
     * @group CRM
     * @covers ::login
     * @dataProvider loginDataProvider
     *
     * @param AuthLoginPayload $authLoginPayload
     * @param LoginUrlToken $loginUrlToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $data
     * @throws BindingResolutionException
     * @throws InvalidAuthLoginTokenTypeException
     * @throws \ReflectionException
     */
    public function testLoginWithOfficeTokenType(
        AuthLoginPayload $authLoginPayload,
        LoginUrlToken $loginUrlToken,
        Scope $fractalScope,
        array $data
    ) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->setToPrivateProperty($authLoginPayload, 'tokenType', self::TOKEN_TYPE_OFFICE);

        $this->officeServiceMock
            ->shouldReceive('login')
            ->once()
            ->with($authLoginPayload->redirectUri, $authLoginPayload->scopes)
            ->andReturn($loginUrlToken);

        $this->fractal
            ->shouldReceive('createData')
            ->once()
            ->andReturn($fractalScope);

        $fractalScope
            ->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $result = $service->login($authLoginPayload);

        $this->assertEquals($data, $result);
    }

    /**
     * @group CRM
     * @covers ::login
     * @dataProvider loginDataProvider
     *
     * @param AuthLoginPayload $authLoginPayload
     * @param LoginUrlToken $loginUrlToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $data
     * @throws BindingResolutionException
     * @throws InvalidAuthLoginTokenTypeException
     * @throws \ReflectionException
     */
    public function testLoginWithAuthState(
        AuthLoginPayload $authLoginPayload,
        LoginUrlToken $loginUrlToken,
        Scope $fractalScope,
        array $data
    ) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->setToPrivateProperty($loginUrlToken, 'authState', true);

        $this->googleServiceMock
            ->shouldReceive('login')
            ->once()
            ->with($authLoginPayload->redirectUri, $authLoginPayload->scopes)
            ->andReturn($loginUrlToken);

        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with([
                'token_type' => $authLoginPayload->tokenType,
                'relation_type' => $authLoginPayload->relationType,
                'relation_id' => $authLoginPayload->relationId,
                'state' => $loginUrlToken->authState
            ]);

        $this->fractal
            ->shouldReceive('createData')
            ->once()
            ->andReturn($fractalScope);

        $fractalScope
            ->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $result = $service->login($authLoginPayload);

        $this->assertEquals($data, $result);
    }

    /**
     * @group CRM
     * @covers ::code
     * @dataProvider codeDataProvider
     *
     * @param string $tokenType
     * @param string $code
     * @param string $redirectUri
     * @param array $scope
     * @param EmailToken $emailToken
     * @throws BindingResolutionException
     * @throws InvalidAuthLoginTokenTypeException
     */
    public function testCode(string $tokenType, string $code, string $redirectUri, array $scope, EmailToken $emailToken) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->gmailServiceMock
            ->shouldReceive('auth')
            ->once()
            ->with($code, $redirectUri)
            ->andReturn($emailToken);

        $result = $service->code($tokenType, $code, $redirectUri, $scope);

        $this->assertEquals($emailToken, $result);
    }

    /**
     * @group CRM
     * @covers ::code
     * @dataProvider codeDataProvider
     *
     * @param string $tokenType
     * @param string $code
     * @param string $redirectUri
     * @param array $scope
     * @param EmailToken $emailToken
     * @throws BindingResolutionException
     * @throws InvalidAuthLoginTokenTypeException
     */
    public function testCodeWithOfficeTokenType(string $tokenType, string $code, string $redirectUri, array $scope, EmailToken $emailToken) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $tokenType = self::TOKEN_TYPE_OFFICE;

        $this->officeServiceMock
            ->shouldReceive('auth')
            ->once()
            ->with($code, $redirectUri, $scope)
            ->andReturn($emailToken);

        $result = $service->code($tokenType, $code, $redirectUri, $scope);

        $this->assertEquals($emailToken, $result);
    }

    /**
     * @group CRM
     * @covers ::code
     * @dataProvider codeDataProvider
     *
     * @param string $tokenType
     * @param string $code
     * @param string $redirectUri
     * @param array $scope
     * @param EmailToken $emailToken
     * @throws BindingResolutionException
     * @throws InvalidAuthLoginTokenTypeException
     */
    public function testCodeWithoutEmailToken(string $tokenType, string $code, string $redirectUri, array $scope, EmailToken $emailToken) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $tokenType = 'wrong_token_type';

        $this->expectException(InvalidAuthCodeTokenTypeException::class);

        $result = $service->code($tokenType, $code, $redirectUri, $scope);

        $this->assertEquals($emailToken, $result);
    }

    /**
     * @group CRM
     * @covers ::authorize
     * @dataProvider authorizeDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @param AuthorizeTokenRequest $request
     * @param LegacyMockInterface|MockInterface|EmailToken $emailToken
     * @param array $emailTokenArray
     * @throws BindingResolutionException
     * @throws InvalidAuthCodeTokenTypeException
     */
    public function testAuthorize(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray,
        AuthorizeTokenRequest $request,
        EmailToken $emailToken,
        array $emailTokenArray
    ) {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->gmailServiceMock
            ->shouldReceive('auth')
            ->once()
            ->with($request->auth_code, $request->redirect_uri)
            ->andReturn($emailToken);

        $emailToken
            ->shouldReceive('toArray')
            ->once()
            ->with(null, $request->token_type, $request->relation_type, $request->relation_id)
            ->andReturn($emailTokenArray);

        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($emailTokenArray)
            ->andReturn($accessToken);

        $this->responseWithGoogleTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->authorize($request);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::authorize
     * @dataProvider authorizeDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @param AuthorizeTokenRequest $request
     * @param LegacyMockInterface|MockInterface|EmailToken $emailToken
     * @param array $emailTokenArray
     * @throws BindingResolutionException
     * @throws InvalidAuthCodeTokenTypeException
     */
    public function testAuthorizeWithOfficeTokenType(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray,
        AuthorizeTokenRequest $request,
        EmailToken $emailToken,
        array $emailTokenArray
    ) {
        $request->offsetSet('token_type', self::TOKEN_TYPE_OFFICE);
        $accessToken->token_type = self::TOKEN_TYPE_OFFICE;

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->officeServiceMock
            ->shouldReceive('auth')
            ->once()
            ->with($request->auth_code, $request->redirect_uri, $request->scopes)
            ->andReturn($emailToken);

        $emailToken
            ->shouldReceive('toArray')
            ->once()
            ->with(null, $request->token_type, $request->relation_type, $request->relation_id)
            ->andReturn($emailTokenArray);

        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($emailTokenArray)
            ->andReturn($accessToken);

        $this->responseWithOfficeTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->authorize($request);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::authorize
     * @dataProvider authorizeDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     * @param AuthorizeTokenRequest $request
     * @param LegacyMockInterface|MockInterface|EmailToken $emailToken
     * @param array $emailTokenArray
     * @throws BindingResolutionException
     * @throws InvalidAuthCodeTokenTypeException
     */
    public function testAuthorizeWithState(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray,
        AuthorizeTokenRequest $request,
        EmailToken $emailToken,
        array $emailTokenArray
    ) {
        $request->offsetSet('state', 'some_state');

        $stateAccessToken = $this->getEloquentMock(AccessToken::class);
        $stateAccessToken->id = self::STATE_TOKEN_ID;

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->tokenRepositoryMock
            ->shouldReceive('getByState')
            ->once()
            ->with($request->state)
            ->andReturn($stateAccessToken);

        $this->gmailServiceMock
            ->shouldReceive('auth')
            ->once()
            ->with($request->auth_code, $request->redirect_uri)
            ->andReturn($emailToken);

        $emailToken
            ->shouldReceive('toArray')
            ->once()
            ->with($stateAccessToken->id, $request->token_type, $request->relation_type, $request->relation_id)
            ->andReturn($emailTokenArray);

        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($emailTokenArray)
            ->andReturn($accessToken);

        $this->responseWithGoogleTokenType($accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray);

        $result = $service->authorize($request);

        $this->assertEquals(array_merge($validateTokenArray, $tokenArray), $result);
    }

    /**
     * @group CRM
     * @covers ::refresh
     * @dataProvider refreshDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param LegacyMockInterface|MockInterface|EmailToken $emailToken
     * @throws BindingResolutionException
     */
    public function testRefresh(AccessToken $accessToken, EmailToken $emailToken)
    {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->googleServiceMock
            ->shouldReceive('refresh')
            ->once()
            ->with($accessToken)
            ->andReturn($emailToken);

        $emailToken
            ->shouldReceive('exists')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $result = $service->refresh($accessToken);

        $this->assertInstanceOf(EmailToken::class, $result);
        $this->assertEquals($emailToken, $result);
    }

    /**
     * @group CRM
     * @covers ::refresh
     * @dataProvider refreshDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param LegacyMockInterface|MockInterface|EmailToken $emailToken
     * @throws BindingResolutionException
     */
    public function testRefreshWithOfficeTokenType(AccessToken $accessToken, EmailToken $emailToken)
    {
        $accessToken->token_type = self::TOKEN_TYPE_OFFICE;

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->officeServiceMock
            ->shouldReceive('refresh')
            ->once()
            ->with($accessToken)
            ->andReturn($emailToken);

        $emailToken
            ->shouldReceive('exists')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $result = $service->refresh($accessToken);

        $this->assertInstanceOf(EmailToken::class, $result);
        $this->assertEquals($emailToken, $result);
    }

    /**
     * @group CRM
     * @covers ::refresh
     * @dataProvider refreshDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param LegacyMockInterface|MockInterface|EmailToken $emailToken
     * @throws BindingResolutionException
     */
    public function testRefreshWithExistsEmailToken(AccessToken $accessToken, EmailToken $emailToken)
    {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $this->googleServiceMock
            ->shouldReceive('refresh')
            ->once()
            ->with($accessToken)
            ->andReturn($emailToken);

        $emailToken
            ->shouldReceive('exists')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        Log::shouldReceive('info');

        $this->tokenRepositoryMock
            ->shouldReceive('refresh')
            ->once()
            ->with($accessToken->id, $emailToken);

        $result = $service->refresh($accessToken);

        $this->assertInstanceOf(EmailToken::class, $result);
        $this->assertEquals($emailToken, $result);
    }

    /**
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     */
    private function responseWithGoogleTokenType(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $this->googleServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validateToken);

        $this->fractal
            ->shouldReceive('createData')
            ->twice()
            ->andReturn($fractalScope);

        $fractalScope
            ->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn($validateTokenArray);

        $fractalScope
            ->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn($tokenArray);
    }

    /**
     * @param AccessToken $accessToken
     * @param ValidateToken $validateToken
     * @param LegacyMockInterface|MockInterface|Scope $fractalScope
     * @param array $validateTokenArray
     * @param array $tokenArray
     */
    private function responseWithOfficeTokenType(
        AccessToken $accessToken,
        ValidateToken $validateToken,
        Scope $fractalScope,
        array $validateTokenArray,
        array $tokenArray
    ) {
        $this->officeServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validateToken);

        $this->fractal
            ->shouldReceive('createData')
            ->twice()
            ->andReturn($fractalScope);

        $fractalScope
            ->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn($validateTokenArray);

        $fractalScope
            ->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn($tokenArray);
    }

    /**
     * @return array[]
     */
    public function authServiceDataProvider(): array
    {
        $accessToken = $this->getEloquentMock(AccessToken::class);

        $accessToken->token_type = self::TOKEN_TYPE;
        $accessToken->relation_type = self::TOKEN_RELATION_TYPE;
        $accessToken->id = self::TOKEN_ID;

        $validateToken = Mockery::mock(ValidateToken::class);

        $fractalScope = Mockery::mock(Scope::class);

        $validateTokenArray = [
            'is_valid' => true,
            'is_expired' => false,
            'message' => 'some_message',
        ];

        $tokenArray = [
            'id' => self::TOKEN_ID,
            'token_type' => 'some_token_type'
        ];

        return [[$accessToken, $validateToken, $fractalScope, $validateTokenArray, $tokenArray]];
    }

    /**
     * @return array[]
     * @throws \ReflectionException
     */
    public function validateCustomDataProvider(): array
    {
        $accessToken = Mockery::mock(CommonToken::class);

        $this->setToPrivateProperty($accessToken, 'tokenType', self::TOKEN_TYPE);

        $validateToken = Mockery::mock(ValidateToken::class);

        return [[$accessToken, $validateToken]];
    }

    /**
     * @return array[]
     * @throws PropertyDoesNotExists
     */
    public function loginDataProvider(): array
    {
        $payload = new AuthLoginPayload([
            'token_type' => self::TOKEN_TYPE,
            'relation_type' => self::TOKEN_RELATION_TYPE,
            'relation_id' => self::AUTH_LOGIN_PAYLOAD_RELATION_ID,
            'redirect_uri' => self::AUTH_LOGIN_PAYLOAD_REDIRECT_URI,
            'scopes' => self::AUTH_LOGIN_PAYLOAD_SCOPES
        ]);

        $loginUrlToken = new LoginUrlToken([
            'login_url' => self::LOGIN_URL_TOKEN_LOGIN_URL,
            'auth_state' => self::LOGIN_URL_TOKEN_AUTH_STATE,
        ]);

        $fractalScope = Mockery::mock(Scope::class);

        $data = [
            'url' => self::LOGIN_URL_TOKEN_LOGIN_URL,
            'state' => self::LOGIN_URL_TOKEN_AUTH_STATE
        ];

        return [[$payload, $loginUrlToken, $fractalScope, $data]];
    }

    /**
     * @return array[]
     * @throws PropertyDoesNotExists
     */
    public function codeDataProvider(): array
    {
        $tokenType = self::TOKEN_TYPE;
        $code = self::TOKEN_CODE;
        $redirectUri = self::AUTH_LOGIN_PAYLOAD_REDIRECT_URI;
        $scope = self::AUTH_LOGIN_PAYLOAD_SCOPES;

        $emailToken = new EmailToken([
            'first_name' => self::EMAIL_TOKEN_FIRST_NAME,
            'last_name' => self::EMAIL_TOKEN_LAST_NAME,
            'email_address' => self::EMAIL_TOKEN_EMAIL_ADDRESS,
        ]);

        return [[$tokenType, $code, $redirectUri, $scope, $emailToken]];
    }

    /**
     * @return array[]
     */
    public function authorizeDataProvider(): array
    {
        $authorizeTokenRequest = new AuthorizeTokenRequest();

        $authorizeTokenRequest->offsetSet('token_type', self::TOKEN_TYPE);
        $authorizeTokenRequest->offsetSet('relation_type', self::TOKEN_RELATION_TYPE);
        $authorizeTokenRequest->offsetSet('relation_id', self::TOKEN_ID);
        $authorizeTokenRequest->offsetSet('auth_code', self::TOKEN_CODE);
        $authorizeTokenRequest->offsetSet('redirect_uri', self::AUTH_LOGIN_PAYLOAD_REDIRECT_URI);
        $authorizeTokenRequest->offsetSet('scopes', self::AUTH_LOGIN_PAYLOAD_SCOPES);

        $emailToken = Mockery::mock(EmailToken::class, [
            'first_name' => self::EMAIL_TOKEN_FIRST_NAME,
            'last_name' => self::EMAIL_TOKEN_LAST_NAME,
            'email_address' => self::EMAIL_TOKEN_EMAIL_ADDRESS,
        ]);

        $emailTokenArray = ['some_array'];

        $data = $this->authServiceDataProvider();

        $data[0][] = $authorizeTokenRequest;
        $data[0][] = $emailToken;
        $data[0][] = $emailTokenArray;

        return $data;
    }

    /**
     * @return array[]
     */
    public function refreshDataProvider(): array
    {
        $accessToken = $this->getEloquentMock(AccessToken::class);

        $accessToken->token_type = self::TOKEN_TYPE;
        $accessToken->relation_type = self::TOKEN_RELATION_TYPE;
        $accessToken->id = self::TOKEN_ID;

        $emailToken = Mockery::mock(EmailToken::class, [
            'first_name' => self::EMAIL_TOKEN_FIRST_NAME,
            'last_name' => self::EMAIL_TOKEN_LAST_NAME,
            'email_address' => self::EMAIL_TOKEN_EMAIL_ADDRESS,
        ]);

        return [[$accessToken, $emailToken]];
    }
}
