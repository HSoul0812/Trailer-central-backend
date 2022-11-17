<?php

namespace Tests\Parts;

use Tests\TestCase;

class ManufacturersGetTest extends TestCase
{
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
        $this->getJson('/api/parts/manufacturers')->assertSuccessful();
    }

}
