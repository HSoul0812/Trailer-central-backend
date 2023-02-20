<?php

namespace Tests\Integration\App\Api\User;

use App\Models\WebsiteUser\WebsiteUser;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\IntegrationTestCase;

class JWTAuthTest extends IntegrationTestCase
{
    /**
     * Test that the system can return error when we try to authenticate with invalid credentials
     *
     * @return void
     */
    public function testItReturnsErrorWhenProvidingInvalidUserCredentials(): void
    {
        $params = http_build_query([
            'email' => $this->faker->safeEmail(),
            'password' => $this->faker->password(),
            'captcha' => Str::random(),
        ]);

        $this->getJson("/api/user/auth?$params")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('message', "Username or password doesn't match");
    }

    /**
     * Test that the system can generate a new JWT token if credentials is valid
     *
     * @return void
     */
    public function testItCanGenerateAJwtTokenWhenCredentialsIsCorrect(): void
    {
        $password = $this->faker->password();

        $websiteUser = WebsiteUser::factory()->create([
            'password' => $password,
        ]);

        $params = http_build_query([
            'email' => $websiteUser->email,
            'password' => $password,
            'captcha' => Str::random(),
        ]);

        $this->getJson("/api/user/auth?$params")
            ->assertOk()
            ->assertSeeText('token');
    }

    /**
     * Test that the system can refresh a JWT token
     *
     * @return void
     */
    public function testItCanRefreshJwtToken(): void
    {
        $password = $this->faker->password();

        $websiteUser = WebsiteUser::factory()->create([
            'password' => $password,
        ]);

        $token = auth('api')->attempt([
            'email' => $websiteUser->email,
            'password' => $password,
        ]);

        $newToken = $this->postJson('/api/user/jwt/refresh', [], $this->headerWithToken($token))
            ->assertOk()
            ->json('token');

        $this->assertNotEquals($token, $newToken);

        $this->getJson('/api/user', $this->headerWithToken($newToken))
            ->assertOk()
            ->assertJsonPath('data.email', $websiteUser->email);
    }

    /**
     * Test that the system can invalidate the JWT token
     *
     * @return void
     */
    public function testItCanLogOutOrInvalidateJwtToken(): void
    {
        $password = $this->faker->password();

        $websiteUser = WebsiteUser::factory()->create([
            'password' => $password,
        ]);

        $token = auth('api')->attempt([
            'email' => $websiteUser->email,
            'password' => $password,
        ]);

        // First we make sure that the token is working
        $this->getJson('/api/user', $this->headerWithToken($token))
            ->assertOk()
            ->assertJsonPath('data.email', $websiteUser->email);

        // Then, we invalidate that token
        $this->postJson('/api/user/jwt/logout', [], $this->headerWithToken($token))
            ->assertOk()
            ->assertJsonPath('message', 'Token invalidated!');

        // Finally, we try to get the user information again using that token
        $this->getJson('/api/user', $this->headerWithToken($token))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * A helper method, use to get the header with token included
     *
     * @param string $token
     * @return string[]
     */
    private function headerWithToken(string $token): array
    {
        return [
            'Authorization' => "Bearer $token",
        ];
    }
}
