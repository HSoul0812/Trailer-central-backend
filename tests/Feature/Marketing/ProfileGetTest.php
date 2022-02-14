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
     * @return void
     */
    public function testGettingProfiles()
    {                    
        $this->json('GET', '/api/marketing/clapp/profile') 
            ->assertStatus(200);
    }
}