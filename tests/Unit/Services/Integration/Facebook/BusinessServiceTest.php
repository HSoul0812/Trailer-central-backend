<?php

namespace Tests\Unit\Services\Integration\Facebook;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Inventory\BusinessService;
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
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @covers ::validate
     *
     * @throws BindingResolutionException
     */
    public function testValidate()
    {
        // Get Access Token
        $accessToken = AccessToken::find($_ENV['TEST_FB_TOKEN_ID']);

        /** @var BusinessService $service */
        $service = $this->app->make(BusinessService::class);

        // Validate Test Service
        $result = $service->validate($accessToken);

        // Assert is Valid
        $this->assertTrue($result['is_valid']);

        // Assert Is Not Expired
        $this->assertFalse($result['is_expired']);
    }
}
