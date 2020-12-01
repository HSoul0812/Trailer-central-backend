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

        // Assert is Valid
        $this->assertTrue($result['is_valid']);

        // Assert Is Not Expired
        $this->assertFalse($result['is_expired']);
    }


    /**
     * Create Access Token With Factory
     */
    private function createAccessToken()
    {
        // Get Access Token
        $time = strtotime($_ENV['TEST_FB_ISSUED_AT']);
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
            'issued_at' => date("Y-m-d H:i:s", $time),
            'scope' => $scopes
        ]);
        dd($accessToken);

        // Get Child Scopes
        /*$relationScopes = array();
        foreach($scopes as $scope) {
            $relationScopes[] = factory(Scope::class)->make([
                'integration_token_id' => $accessToken->id,
                'scope' => $scope
            ]);
        }
        $collectScopes = new Collection($relationScopes);
        $accessToken->setRelation('scopes', $collectScopes);
        $accessToken->scope = $scopes;
        var_dump($accessToken);
        var_dump($accessToken->scopes);
        var_dump($accessToken->scope);
        die;*/

        // Return Access Token
        return $accessToken;
    }
}
