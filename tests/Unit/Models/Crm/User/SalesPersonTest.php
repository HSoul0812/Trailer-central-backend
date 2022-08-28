<?php

namespace Tests\Unit\Models\Crm\User;

use App\Models\CRM\User\SalesPerson;
use PHPUnit\Framework\TestCase;

class SalesPersonTest extends TestCase
{
    /**
     * @group CRM
     */
    public function testCanInstantiate()
    {
        $salesPerson = new SalesPerson();
        $this->assertInstanceOf(SalesPerson::class, $salesPerson);
    }

    /**
     * @group CRM
     */
    public function testCanCreateByFill()
    {
        // this will throw an error now because first_name is not mass-assignable
        $salesPerson = new SalesPerson([
            'first_name' => 'sample123',
            // add other fields
            // add other fields
            // add other fields
        ]);

        // test assigned data
        $this->assertTrue($salesPerson->first_name === 'sample123');
        // ...test other fields
    }

}
