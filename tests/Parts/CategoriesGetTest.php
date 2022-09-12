<?php

namespace Tests\Parts;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\TestCase;


class CategoriesGetTest extends TestCase
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
    public function testGettingCategoriesNoFilters()
    {                    
        $this->json('GET', '/api/parts/categories') 
            ->seeStatusCode(200);             
    }
    
//    public function testBrandSearch()
//    {            
//                   
//    }
    
}
