<?php

namespace Tests\Parts;

use Tests\TestCase;

class BrandsGetTest extends TestCase
{
    /**
     * Test getting brands
     *
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testGettingBrandsNoFilters()
    {
        $this->getJson('/api/parts/brands')->assertSuccessful();
    }
}
