<?php

namespace Tests\Parts;

use Tests\TestCase;

class TypesGetTest extends TestCase
{
    /**
     * Test getting brands
     *
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testGettingTypesNoFilters()
    {
        $this->getJson('/api/parts/types')->assertSuccessful();
    }
}
