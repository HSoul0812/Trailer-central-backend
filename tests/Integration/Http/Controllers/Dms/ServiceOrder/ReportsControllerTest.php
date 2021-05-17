<?php

namespace Tests\Integration\Http\Controllers\Dms\ServiceOrder;

use Tests\TestCase;

/**
 * Class ReportsControllerTest
 * @package Tests\Integration\Http\Controllers\Dms\ServiceOrder
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Dms\ServiceOrder\ReportsController
 */
class ReportsControllerTest extends TestCase
{
    /**
     * @covers ::monthly
     */
    public function testMonthly()
    {
        $response = $this->json('GET', '/api/dms/reports/service-monthly-hours');

        $response->assertStatus(403);
    }

    /**
     * @covers ::monthly
     */
    public function testMonthlyWithoutAccessToken()
    {
        $response = $this->json('GET', '/api/dms/reports/service-monthly-hours');

        $response->assertStatus(403);
    }
}
