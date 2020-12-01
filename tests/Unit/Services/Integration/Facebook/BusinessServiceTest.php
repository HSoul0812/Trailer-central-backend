<?php

namespace Tests\Unit\Services\Integration\Facebook;

use App\Models\Integration\Auth\AccessToken;
use App\Models\Integration\Auth\Scope;
use App\Services\Integration\Facebook\BusinessService;
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
        $accessToken = $this->createAccessToken();

        /** @var BusinessService $service */
        $service = $this->app->make(BusinessService::class);

        // Validate Test Service
        $result = $service->validate($accessToken);
        var_dump($accessToken);
        var_dump($result);

        // Assert is Valid
        $this->assertTrue($result['is_valid']);

        // Assert Is Not Expired
        $this->assertFalse($result['is_expired']);
    }


    /**
     * Create Access Token With Factory
     */
    private function createAccessToken() {
        
        // Get Access Token
        $time = time();
        $scopes = explode(" ", $_ENV['TEST_FB_SCOPES']);
        $accessToken = factory(AccessToken::class)->make([
            'token_type' => 'facebook',
            'relation_type' => 'fbapp_catalog',
            'relation_id' => $_ENV['TEST_FB_RELATION_ID'],
            'access_token' => $_ENV['TEST_FB_ACCESS_TOKEN'],
            'refresh_token' => $_ENV['TEST_FB_REFRESH_TOKEN'],
            'id_token' => $_ENV['TEST_FB_ID_TOKEN'],
            'expires_in' => $_ENV['TEST_FB_EXPIRES_IN'],
            'expires_at' => date("Y-m-d H:i:s", $time + $_ENV['TEST_FB_EXPIRES_IN']),
            'issued_at' => date("Y-m-d H:i:s", $time)
        ]);
        $accessToken->scope = $_ENV['TEST_FB_SCOPES'];

        // Create Scopes
        /*$tokenScopes = array();
        foreach($scopes as $scope) {
            $tokenScopes[] = factory(Scope::class)->make(['scope' => $scope])->toArray();
        }
        $accessToken->scopes()->createMany($tokenScopes);*/

        // Return Access Token
        return $accessToken;
    }
}
