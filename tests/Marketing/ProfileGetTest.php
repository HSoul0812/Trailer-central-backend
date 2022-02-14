<?php

namespace Tests\Marketing\Profile;

use Tests\TestCase;


class ProfileGetTest extends TestCase
{
    
    public function __construct() {
        parent::__construct();   
    }
    
    /**
     * Test getting profiles
     *
     * @return void
     */
    public function testGettingProfiles()
    {                    
        $this->json('GET', '/api/marketing/profile') 
            ->assertStatus(200);             
    }
}