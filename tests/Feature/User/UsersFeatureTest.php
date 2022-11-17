<?php

namespace Tests\Feature\User;

use App\Models\User\User;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UsersFeatureTest extends TestCase
{
    use WithFaker;

    /**
     * @group DMS
     * @group DMS_USER_FEATURES
     *
     * @return void
     */
    public function testCreateUser()
    {
        $email = $this->faker->email;
        $response = $this->post('/api/users', [
            'email' => $email,
            'password' => $this->faker->regexify('[A-Za-z0-9]{15}'),
            'name' => $this->faker->name,
            'clsf_active' => $this->faker->randomElement([0, 1]),
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'email' => $email,
            ],
        ]);
        User::where('email', $email)->delete();
    }

    /**
     * @group DMS
     * @group DMS_USER_FEATURES
     *
     * @return void
     */
    public function testCreateUserUsingInvalidEmail()
    {
        $response = $this->post('/api/users', [
            'email' => 'invalid email',
            'password' => $this->faker->password(6, 7),
            'name' => $this->faker->name,
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @group DMS
     * @group DMS_USER_FEATURES
     *
     * @return void
     */
    public function testCreateUserUsingInvalidPassword()
    {
        $response = $this->post('/api/users', [
            'email' => $this->faker->email,
            'password' => $this->faker->password(6, 7),
            'name' => $this->faker->name,
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @group DMS
     * @group DMS_USER_FEATURES
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testGetUserWithExistingEmail()
    {
        $user = factory(User::class)->create();
        $response = $this->get('/api/users?email=' . $user->email);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'email' => $user->email,
                'name' => $user->name,
            ],
        ]);
        $user->delete();
    }

    /**
     * @group DMS
     * @group DMS_USER_FEATURES
     *
     * @return void
     */
    public function testGetUserWithNonExistingEmail()
    {
        $email = $this->faker->unique()->email;
        $response = $this->get('/api/users?email=' . $email, [
            'email' => $email,
        ]);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
