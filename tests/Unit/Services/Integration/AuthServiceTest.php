<?php

namespace Tests\Unit\Services\Integration;

use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\AuthService;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Models\Integration\Auth\AccessToken;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery;
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
    /**
     * @var LegacyMockInterface|TokenRepositoryInterface
     */
    private $tokenRepositoryMock;

    /**
     * @var LegacyMockInterface|GoogleServiceInterface
     */
    private $googleServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->googleServiceMock = Mockery::mock(GoogleServiceInterface::class);
        $this->app->instance(GoogleServiceInterface::class, $this->googleServiceMock);

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);
    }

    /**
     * @covers ::index
     *
     * @throws BindingResolutionException
     */
    public function testIndex()
    {
        // Get Test Token
        $accessToken = factory(AccessToken::class)->create();
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Index Request Params
        $indexRequestParams = [
            'token_type' => $accessToken->token_type,
            'relation_type' => $accessToken->relation_type,
            'relation_id' => $tokenId
        ];

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        // Mock Get Token
        $this->tokenRepositoryMock
            ->shouldReceive('getRelation')
            ->once()
            ->with($indexRequestParams)
            ->andReturn($accessToken);

        // Mock Validate Access Token
        $this->googleServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validate);

        // Validate Show Catalog Result
        $result = $service->index($indexRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $tokenId);
    }

    /**
     * @covers ::show
     *
     * @throws BindingResolutionException
     */
    public function testShow()
    {
        // Get Test Token
        $accessToken = factory(AccessToken::class)->create();
        $validate = ['is_valid' => true, 'is_expired' => false];

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        // Mock Get Token
        $this->tokenRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $tokenId])
            ->andReturn($accessToken);

        // Mock Validate Access Token
        $this->googleServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validate);

        // Validate Show Catalog Result
        $result = $service->show($tokenId);

        // Assert Match
        $this->assertSame($result['data']['id'], $tokenId);
    }

    /**
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreate()
    {
        // Get Test Token
        $accessToken = factory(AccessToken::class)->create();
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Create Request Params
        $createRequestParams = [
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'refresh_token' => $accessToken->refresh_token,
            'expires_in' => $accessToken->expires_in,
            'expires_at' => $accessToken->expires_at,
            'issued_at' => $accessToken->issued_at
        ];

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        // Mock Create Catalog Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createRequestParams)
            ->andReturn($accessToken);

        // Mock Validate Access Token
        $this->googleServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validate);

        // Validate Create Catalog Result
        $result = $service->create($createRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $tokenId);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }

    /**
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdate()
    {
        // Get Test Token
        $accessToken = factory(AccessToken::class)->create();
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Update Request Params
        $updateRequestParams = [
            'id' => $accessToken->id,
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'refresh_token' => $accessToken->refresh_token,
            'expires_in' => $accessToken->expires_in,
            'expires_at' => $accessToken->expires_at,
            'issued_at' => $accessToken->issued_at
        ];

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        // Mock Update Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($updateRequestParams)
            ->andReturn($accessToken);

        // Mock Validate Access Token
        $this->googleServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validate);

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $tokenId);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }

    /**
     * @covers ::validate
     *
     * @throws BindingResolutionException
     */
    public function testValidate()
    {
        // Get Test Token
        $accessToken = factory(AccessToken::class)->create();
        $validate = ['is_valid' => true, 'is_expired' => false];

        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        // Mock Validate Access Token
        $this->googleServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validate);

        // Validate Show Catalog Result
        $result = $service->validate($accessToken);

        // Assert Match
        $this->assertSame($result, $validate);
    }
}
