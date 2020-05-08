<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;


class ManufacturersGetTest extends TestCase
{
    
    public function __construct() {
        parent::__construct();   
    }
    
    /**
     * Test getting brands
     *
     * @return void
     */
    public function testGettingManufacturersNoFilters()
    {                    
        $this->json('GET', '/api/parts/manufacturers') 
            ->seeStatusCode(200);             
    }
    
//    public function testBrandSearch()
//    {            
//                   
//    }
    
}