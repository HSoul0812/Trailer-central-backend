<?php

namespace Tests\Feature\Marketing;

use Tests\TestCase;


class ProfileGetTest extends TestCase
{
    
    public function __construct() {
        parent::__construct();   
    }
    
    /**
     * Test getting profiles
     *
     * @group Marketing
     * @return void
     */
    public function testGettingProfiles()
    {                    
        $this->withHeaders(['access-token' => $this->accessToken()])
            ->json('GET', '/api/marketing/clapp/profile') 
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'profile',
                        'username',
                        'category'
                    ]
                ]
            ]);
    }
}