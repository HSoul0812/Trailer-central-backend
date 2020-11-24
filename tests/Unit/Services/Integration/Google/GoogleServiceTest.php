<?php

namespace Tests\Unit\Services\Integration\Google;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Google\GoogleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery;
use Google_Client;
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

        $this->googleClientMock = Mockery::mock(Google_Client::class);
        $googleClientMock = $this->googleClientMock;

        $this->app->addContextualBinding(
            GoogleService::class,
            Google_Client::class,
            function() use($googleClientMock) {
                return $googleClientMock;
            }
        );
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

        // Index Request Params
        $setAccessTokenParams = [
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at)
        ];

        /** @var GoogleService $service */
        $service = $this->app->make(GoogleService::class);

        // Mock Set Access Token
        $this->googleClientMock
            ->shouldReceive('setApplicationName')
            ->once()
            ->with($_ENV['GOOGLE_OAUTH_APP_NAME']);

        // Mock Set Client ID
        $this->googleClientMock
            ->shouldReceive('setClientId')
            ->once()
            ->with($_ENV['GOOGLE_OAUTH_CLIENT_ID']);

        // Mock Set Access Token
        $this->googleClientMock
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($setAccessTokenParams);

        // Mock Set Scopes
        $this->googleClientMock
            ->shouldReceive('setScopes')
            ->once()
            ->with($accessToken->scopes);

        // Mock Validate ID Token
        $this->googleClientMock
            ->shouldReceive('verifyIdToken')
            ->once()
            ->with($accessToken->id_token)
            ->andReturn(true);

        // Mock Is Access Token Expired
        $this->googleClientMock
            ->shouldReceive('isAccessTokenExpired')
            ->once()
            ->andReturn(false);

        // Validate Show Catalog Result
        $result = $service->validate($accessToken);

        // Assert True
        //$this->assertTrue($result['is_valid']);

        // Assert False
        //$this->assertFalse($result['is_expired']);
    }
}
