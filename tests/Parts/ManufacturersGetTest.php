<?php

namespace Tests\Parts;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\TestCase;


class ManufacturersGetTest extends TestCase
{
    
    public function __construct() {
        parent::__construct();   
    }
    
    /**
     * Test getting brands
     *
     * @group DMS
     * @group DMS_PARTS
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
