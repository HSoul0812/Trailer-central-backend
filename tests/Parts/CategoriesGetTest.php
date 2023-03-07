<?php

namespace Tests\Parts;

use Tests\TestCase;

class CategoriesGetTest extends TestCase
{
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
        $this->getJson('/api/parts/categories')->assertSuccessful();
    }
}
