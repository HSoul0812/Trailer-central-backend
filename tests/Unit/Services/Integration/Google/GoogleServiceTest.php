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
     * @covers ::index
     *
     * @throws BindingResolutionException
     */
    public function testValidate()
    {
        // Get Test Token
        $tokenId = (int) $_ENV['TEST_AUTH_TOKEN_ID'];
        $accessToken = AccessToken::find($tokenId);

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
