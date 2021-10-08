<?php
namespace Tests\Integration\Http\Controllers\Website\User;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\Website\User\WebsiteUserSeeder;
use Tests\database\seeds\Website\WebsiteSeeder;
use Tests\TestCase;

/**
 * Class WebsiteUserControllerTest
 * @package Tests\Integration\Http\Controllers\Website\User
 * @coversDefaultClass \App\Http\Controllers\v1\Website\User\WebsiteUserController
 */
class WebsiteUserControllerTest extends TestCase {
    use DatabaseTransactions;
    /**
     * @var WebsiteSeeder
     */
    private $websiteSeeder;

    /**
     * @var WebsiteUserSeeder
     */
    private $websiteUserSeeder;

    public function setUp(): void
    {
        parent::setUp();
        $this->websiteSeeder = new WebsiteSeeder();
        $this->websiteUserSeeder = new WebsiteUserSeeder();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testCreateSuccess() {
        $this->websiteSeeder->seed();
        $data = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'middle_name' => 'Middle Name',
            'email' => 'email@email.com',
            'password' => '12345678',
        ];
        $websiteId = $this->websiteSeeder->website->id;
        $response = $this->json('POST', "/api/website/$websiteId/user/signup", $data);
        $response->assertStatus(JsonResponse::HTTP_OK);

        $newUser = $response->getOriginalContent();

        $this->assertEquals($newUser->website_id, $websiteId);
        $response->assertJson([
            'data'=> [
                'access_token' => $newUser->token->access_token,
                'user' => [
                    'id' => $newUser->id,
                    'email' => $newUser->email,
                    'website_id' => $newUser->website_id
                ]
            ]
        ]);
    }

    public function testCreateFail() {
        $this->websiteSeeder->seed();
        $data = [
            'last_name' => 'Last Name',
            'middle_name' => 'Middle Name',
            'email' => 'email',
            'password' => '12345',
        ];
        $websiteId = $this->websiteSeeder->website->id;
        $response = $this->json('POST', "/api/website/$websiteId/user/signup", $data);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testLoginSuccess() {
        $this->websiteUserSeeder->seed();
        $data = [
            'email' => $this->websiteUserSeeder->websiteUser->email,
            'password' => $this->websiteUserSeeder->password,
        ];
        $websiteId = $this->websiteUserSeeder->website->id;
        $websiteUser = $this->websiteUserSeeder->websiteUser;
        $response = $this->json('POST', "/api/website/$websiteId/user/login", $data);
        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'data'=> [
                'access_token' => $websiteUser->token->access_token,
                'user' => [
                    'id' => $websiteUser->id,
                    'email' => $websiteUser->email,
                    'website_id' => $websiteUser->website_id
                ]
            ]
        ]);
    }

    public function testLoginFailWithInvalidInput() {
        $data = [
            'email' => 'email',
            'password' => '12345',
        ];
        $response = $this->json('POST', "/api/website/123/user/login", $data);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testLoginFailWithInvalidPassword() {
        $this->websiteUserSeeder->seed();
        $data = [
            'email' => $this->websiteUserSeeder->websiteUser->email,
            'password' => '1233663',
        ];
        $websiteId = $this->websiteUserSeeder->website->id;
        $response = $this->json('POST', "/api/website/$websiteId/user/login", $data);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function testLoginFailWithInvalidUser() {
        $this->websiteUserSeeder->seed();
        $data = [
            'email' => 'email@email.com',
            'password' => $this->websiteUserSeeder->password,
        ];
        $websiteId = $this->websiteUserSeeder->website->id;
        $response = $this->json('POST', "/api/website/$websiteId/user/login", $data);
        var_dump($response);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
    }
}
