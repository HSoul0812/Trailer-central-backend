<?php

namespace Tests\Unit\Services\Integration\Facebook;

use App\Models\Integration\Auth\AccessToken;
use App\Models\Integration\Auth\Scope;
use App\Services\Integration\Facebook\BusinessService;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;

/**
 * Test for App\Services\Integration\Facebook\BusinessService
 *
 * Class BusinessServiceTest
 * @package Tests\Unit\Services\Integration\Facebook
 *
 * @coversDefaultClass \App\Services\Inventory\BusinessService
 */
class BusinessServiceTest extends TestCase
{
    /**
     * @const Catalog Scopes
     */
    const TEST_EXPIRES_IN = 60 * 60 * 24 * 60;


    /**
     * @var LegacyMockInterface|BusinessServiceInterface
     */
    private $businessServiceMock;


    public function setUp(): void
    {
        parent::setUp();

        $this->businessServiceMock = Mockery::mock(BusinessServiceInterface::class);
        $this->app->instance(BusinessServiceInterface::class, $this->businessServiceMock);
    }

    /**
     * @group Marketing
     * @covers ::validate
     *
     * @throws BindingResolutionException
     */
    public function testValidate()
    {
        // Mock AccessToken
        $time = now();
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = 1;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::DEFAULT_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::DEFAULT_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);

        // Pass Through Business Service Mock
        $this->businessServiceMock
             ->shouldReceive('validate')
             ->passthru();


        /** @var BusinessService $service */
        $service = $this->app->make(BusinessService::class);

        // Validate Test Service
        $scopes = explode(' ', config('oauth.fb.' . $this->type . '.scopes'));
        $result = $service->validate($accessToken, $scopes);

        // Assert is Valid
        $this->assertTrue($result['is_valid']);

        // Assert Is Not Expired
        $this->assertFalse($result['is_expired']);
    }
}
