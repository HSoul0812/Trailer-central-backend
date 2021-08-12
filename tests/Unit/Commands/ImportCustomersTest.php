<?php

namespace Tests\Unit\Commands;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImportCustomersTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandleWithFile()
    {
        $this->artisan('crm:dms:import-customers 1001 crm-trailercentral-dev test_customer_import.csv')
            ->assertExitCode(0);
    }
}
