<?php

namespace Tests\Integration\Http\Controllers\Dms\ServiceOrder;

use Tests\database\seeds\Dms\InvoiceSeeder;
use Tests\database\seeds\Dms\ServiceOrderSeeder;
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
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     */
    public function testMonthly()
    {
        $serviceOrderSeeder = new ServiceOrderSeeder();
        $serviceOrderSeeder->seed();

        $invoiceSeeders = [];

        $expectedSumOfUnitPrices = 0;

        $currentDate = new \DateTime();
        $currentMonth = $currentDate->format('F');
        $currentYear = $currentDate->format('Y');

        foreach ($serviceOrderSeeder->serviceOrders as $serviceOrder) {
            $unitPrice = rand(10, 100);
            $expectedSumOfUnitPrices += $unitPrice;

            $invoiceSeeder = new InvoiceSeeder([
                'serviceOrder' => $serviceOrder,
                'invoiceItem' => [
                    'unit_price' => $unitPrice
                ]
            ]);

            $invoiceSeeder->seed();
            $invoiceSeeders[] = $invoiceSeeder;
        }

        $response = $this->json('GET', '/api/dms/reports/service-monthly-hours', [], ['access-token' => $serviceOrderSeeder->authToken->access_token]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertNotEmpty($responseJson['data']);
        $this->assertCount(1, $responseJson['data']);

        $currentItem = $responseJson['data'][0];

        $this->assertArrayHasKey('month_name', $currentItem);
        $this->assertArrayHasKey('type', $currentItem);
        $this->assertArrayHasKey('unit_price', $currentItem);
        $this->assertArrayHasKey('created_at', $currentItem);

        $this->assertEquals($expectedSumOfUnitPrices, $currentItem['unit_price']);

        $this->assertStringContainsString($currentMonth, $currentItem['month_name']);
        $this->assertStringContainsString($currentYear, $currentItem['month_name']);

        $serviceOrderSeeder->cleanUp();

        foreach ($invoiceSeeders as $invoiceSeeder) {
            $invoiceSeeder->cleanUp();
        }
    }

    /**
     * @covers ::monthly
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     */
    public function testMonthlyWithoutAccessToken()
    {
        $response = $this->json('GET', '/api/dms/reports/service-monthly-hours');

        $response->assertStatus(403);
    }
}
