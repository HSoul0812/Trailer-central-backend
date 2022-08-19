<?php

namespace Tests\Unit\Services\Integration;

use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\AuthService;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
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
use ReflectionProperty;
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

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|TokenRepositoryInterface
     */
    private $tokenRepositoryMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|GoogleServiceInterface
     */
    private $googleServiceMock;

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
     */
    public function testValidateCustomWithOfficeTokenType(CommonToken $accessToken, ValidateToken $validateToken)
    {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $reflector = new ReflectionProperty(CommonToken::class, 'tokenType');
        $reflector->setAccessible(true);
        $reflector->setValue($accessToken, self::TOKEN_TYPE_OFFICE);

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
     */
    public function validateCustomDataProvider(): array
    {
        $accessToken = Mockery::mock(CommonToken::class);

        $reflector = new ReflectionProperty(CommonToken::class, 'tokenType');
        $reflector->setAccessible(true);
        $reflector->setValue($accessToken, self::TOKEN_TYPE);

        print_r($accessToken->tokenType);

        $validateToken = Mockery::mock(ValidateToken::class);

        return [[$accessToken, $validateToken]];
    }
}
