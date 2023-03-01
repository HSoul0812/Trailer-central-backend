<?php

namespace Tests\Feature\Marketing\Craigslist;

use Tests\TestCase;
use Tests\database\seeds\Marketing\Craigslist\ProfileSeeder;


class ProfileGetTest extends TestCase
{
    /**
     * @var ProfileSeeder
     */
    private $seeder;


    /**
     * Test getting profiles
     *
     * @group Marketing
     * @return void
     */
    public function testGettingProfiles()
    {
        $this->seeder->seed();

        $this->withHeaders(['access-token' => $this->seeder->authToken->access_token])
            ->json('GET', '/api/marketing/clapp/profile')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'profiles' => [
                        'data' => [
                            '*' => [
                                'id',
                                'profile',
                                'username',
                                'category'
                            ]
                        ]
                    ]
                ]
            ]);
    }


    /**
     * Set Up Seeder
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make Profile Seeder
        $this->seeder = new ProfileSeeder();
    }

    /**
     * Tear Down Seeder
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }
}
