<?php

namespace Tests\Unit\Services\CRM\User;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\User\SalesAuthService;
use App\Services\Integration\AuthServiceInterface;
use App\Models\CRM\User\SalesPerson;
use App\Models\Integration\Auth\AccessToken;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\User\SalesAuthService
 *
 * Class SalesAuthServiceTest
 * @package Tests\Unit\Services\Auth
 *
 * @coversDefaultClass \App\Services\CRM\User\SalesAuthService
 */
class SalesAuthServiceTest extends TestCase
{
    /**
     * @var LegacyMockInterface|SalesPersonRepositoryInterface
     */
    private $salesPersonRepositoryMock;

    /**
     * @var LegacyMockInterface|TokenRepositoryInterface
     */
    private $tokenRepositoryMock;

    /**
     * @var LegacyMockInterface|AuthServiceInterface
     */
    private $authServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->authServiceMock = Mockery::mock(AuthServiceInterface::class);
        $this->app->instance(AuthServiceInterface::class, $this->authServiceMock);

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);

        $this->salesPersonRepositoryMock = Mockery::mock(SalesPersonRepositoryInterface::class);
        $this->app->instance(SalesPersonRepositoryInterface::class, $this->salesPersonRepositoryMock);
    }

    /**
     * @group CRM
     * @covers ::show
     *
     * @throws BindingResolutionException
     */
    public function testShow()
    {
        // Get Test Sales Person
        $accessToken = factory(AccessToken::class)->make();
        $salesPerson = factory(SalesPerson::class)->make([
            'id' => (int) $accessToken->relation_id
        ]);

        // Get Test Token
        $validate = ['is_valid' => true, 'is_expired' => false];
        
        // Show Request Params
        $getRelationParams = [
            'token_type' => 'google',
            'relation_type' => 'sales_person',
            'relation_id' => $accessToken->relation_id
        ];

        /** @var SalesAuthService $service */
        $service = $this->app->make(SalesAuthService::class);

        // Mock Get Token
        $this->tokenRepositoryMock
            ->shouldReceive('getRelation')
            ->once()
            ->with($getRelationParams)
            ->andReturn($accessToken);

        // Mock Sales Person Repository
        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => $accessToken->relation_id])
            ->andReturn($salesPerson);

        // Mock Sales Person Repository
        $this->authServiceMock
            ->shouldReceive('response')
            ->once()
            ->andReturn([
                'sales_person' => $salesPerson,
                'data' => $accessToken,
                'validate' => $validate
            ]);

        // Validate Show Catalog Result
        $result = $service->show([
            'token_type' => 'google',
            'id' => (int) $accessToken->relation_id
        ]);

        // Assert Match
        $this->assertSame($result['sales_person']['id'], (int) $accessToken->relation_id);

        // Assert Match
        $this->assertSame($result['data']['id'], $accessToken->id);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }

    /**
     * @group CRM
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreate()
    {
        // Get Test Sales Person
        $accessToken = factory(AccessToken::class)->make();
        $salesPerson = factory(SalesPerson::class)->make([
            'user_id' => (int) $accessToken->relation_id
        ]);
        $salesPerson->id = $accessToken->relation_id;
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Create Request Params
        $createRequestParams = [
            'user_id' => (int) $accessToken->relation_id,
            'token_type' => 'google',
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'refresh_token' => $accessToken->refresh_token,
            'expires_in' => $accessToken->expires_in,
            'expires_at' => $accessToken->expires_at,
            'issued_at' => $accessToken->issued_at,
            'email' => $salesPerson->email
        ];

        // Create Auth Params
        $createAuthParams = $createRequestParams;
        unset($createAuthParams['id']);
        $createAuthParams['token_type'] = 'google';
        $createAuthParams['relation_type'] = 'sales_person';
        $createAuthParams['relation_id'] = $accessToken->relation_id;

        /** @var SalesAuthService $service */
        $service = $this->app->make(SalesAuthService::class);

        // Mock Create Catalog Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createAuthParams)
            ->andReturn($accessToken);

        // Mock Sales Person Repository
        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['sales_person_id' => $accessToken->relation_id])
            ->andReturn($salesPerson);
        $this->salesPersonRepositoryMock
            ->shouldReceive('getByEmail')
            ->twice()
            ->with($createRequestParams['user_id'], $createRequestParams['email'])
            ->andReturn($salesPerson);
        $this->salesPersonRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->withAnyArgs()
            ->andReturn($salesPerson);

        // Mock Sales Person Repository
        $this->authServiceMock
            ->shouldReceive('response')
            ->once()
            ->andReturn([
                'sales_person' => $salesPerson,
                'data' => $accessToken,
                'validate' => $validate
            ]);

        // Validate Create Catalog Result
        $result = $service->create($createRequestParams);

        // Assert Match
        $this->assertSame($result['sales_person']['id'], (int) $accessToken->relation_id);

        // Assert Match
        $this->assertSame($result['data']['id'], $accessToken->id);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }

    /**
     * @group CRM
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdate()
    {
        // Get Test Sales Person
        $accessToken = factory(AccessToken::class)->make();
        $salesPerson = factory(SalesPerson::class)->make([
            'id' => (int) $accessToken->relation_id
        ]);
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Update Request Params
        $updateRequestParams = [
            'id' => (int) $accessToken->relation_id,
            'token_type' => 'google',
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'refresh_token' => $accessToken->refresh_token,
            'expires_in' => $accessToken->expires_in,
            'expires_at' => $accessToken->expires_at,
            'issued_at' => $accessToken->issued_at
        ];

        // Update Auth Params
        $updateAuthParams = $updateRequestParams;
        unset($updateAuthParams['id']);
        $updateAuthParams['token_type'] = 'google';
        $updateAuthParams['relation_type'] = 'sales_person';
        $updateAuthParams['relation_id'] = $accessToken->relation_id;

        /** @var SalesAuthService $service */
        $service = $this->app->make(SalesAuthService::class);

        // Mock Update Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($updateAuthParams)
            ->andReturn($accessToken);

        // Mock Sales Person Repository
        $this->salesPersonRepositoryMock
            ->shouldReceive('get')
            ->twice()
            ->with(['sales_person_id' => $accessToken->relation_id])
            ->andReturn($salesPerson);

        // Mock Sales Person Repository
        $this->authServiceMock
            ->shouldReceive('response')
            ->once()
            ->andReturn([
                'sales_person' => $salesPerson,
                'data' => $accessToken,
                'validate' => $validate
            ]);

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);

        // Assert Match
        $this->assertSame($result['sales_person']['id'], (int) $accessToken->relation_id);

        // Assert Match
        $this->assertSame($result['data']['id'], $accessToken->id);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }
}
