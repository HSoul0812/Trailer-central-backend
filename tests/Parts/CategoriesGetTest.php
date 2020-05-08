<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;


class CategoriesGetTest extends TestCase
{
    
    public function __construct() {
        parent::__construct();   
    }
    
    /**
     * Test getting brands
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