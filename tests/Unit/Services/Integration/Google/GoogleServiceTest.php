<?php

namespace Tests\Unit\Services\Integration\Google;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Google\GoogleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;

/**
 * Test for App\Services\Integration\GoogleService
 *
 * Class GoogleServiceTest
 * @package Tests\Unit\Services\Integration
 *
 * @coversDefaultClass \App\Services\Integration\Auth\GoogleService
 */
class GoogleServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group CRM
     * @covers ::login
     *
     * @throws BindingResolutionException
     */
    public function testLogin()
    {
        // Get Test Token
        $accessToken = factory(AccessToken::class)->make();

        /** @var GoogleService $service */
        $service = $this->app->make(GoogleService::class);

        // Validate Show Catalog Result
        $result = $service->login($_ENV['TEST_AUTH_REDIRECT_URI'], $accessToken->scopes);

        // Assert Login URL Exists
        $this->assertTrue(!empty($result));
    }

    /**
     * @group CRM
     * @covers ::refresh
     *
     * @throws BindingResolutionException
     */
    public function testRefresh()
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
    public function testValidate()
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
}
