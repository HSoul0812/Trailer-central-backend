<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Inventory;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use Dingo\Api\Exception\ResourceException;
use Tests\Integration\IntegrationTestCase;
use App\Exceptions\Common\BusyJobException;
use Tests\database\seeds\Inventory\InventorySeeder;
use App\Jobs\Inventory\InventoryBulkUpdateManufacturer;
use App\Http\Controllers\v1\Inventory\InventoryBulkUpdateController;
use App\Http\Requests\Inventory\InventoryBulkUpdateManufacturerRequest;

/**
 * class InventoryBulkUpdateControllerTest
 *
 * @covers \App\Http\Controllers\v1\Inventory\InventoryBulkUpdateController
 * @group MonitoredJobs
 */
class InventoryBulkUpdateControllerTest extends IntegrationTestCase
{

    /**
     * @dataProvider validParametersForUpdateManufacturerProvider
     *
     * @covers ::bulkUpdateManufacturer
     *
     * @param array $params
     *
     * @throws BusyJobException
     */
    public function testUpdateManufacturerWithValidParameters(array $params): void
    {
        // Given an Inventory
        $inventorySeeder = new InventorySeeder();
        $inventorySeeder->seed();

        // And I'm using the controller "InventoryBulkUpdateController"
        $controller = app(InventoryBulkUpdateController::class);

        // And I have a bad formed "InventoryBulkUpdateManufacturerRequest" request
        $request = new InventoryBulkUpdateManufacturerRequest($inventorySeeder->extractValues($params));

        Bus::fake();

        // When I call the bulkUpdateManufacturer action using the well-formed request
        $response = $controller->bulkUpdateManufacturer($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(InventoryBulkUpdateManufacturer::class);
        // And I should see that response status is 200
        self::assertEquals(JsonResponse::HTTP_OK, $response->status());

        $inventorySeeder->cleanUp();
    }

    /**
     * @dataProvider invalidParametersForUpdateManufacturerProvider
     *
     * @covers ::bulkUpdateManufacturer
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @throws BusyJobException
     */
    public function testUpdateManufacturerWithWrongParameters(
        array $params,
        string $expectedException,
        string $expectedExceptionMessage,
        ?string $firstExpectedErrorMessage): void
    {
        // Given an Inventory
        $inventorySeeder = new InventorySeeder();
        $inventorySeeder->seed();

        // And I'm using the controller "InventoryBulkUpdateController"
        $controller = app(InventoryBulkUpdateController::class);

        // And I have a bad formed "InventoryBulkUpdateManufacturerRequest" request
        $request = new InventoryBulkUpdateManufacturerRequest($inventorySeeder->extractValues($params));

        // Then I expect to see a specific exception to be thrown
        $this->expectException($expectedException);
        // And I also expect to see a specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the bulkUpdateManufacturer action using the bad formed request
            $controller->bulkUpdateManufacturer($request);
            $inventorySeeder->cleanUp();
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());
            $inventorySeeder->cleanUp();

            throw $exception;
        }
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function validParametersForUpdateManufacturerProvider(): array
    {
        return [
            'Valid Data'  => [[
                'from_manufacturer' => 'Testing Inventory Before',
                'to_manufacturer' => 'Testing Inventory After'
            ]]
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function invalidParametersForUpdateManufacturerProvider(): array
    {
        return [                   // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'Empty request'        => [[], ResourceException::class, 'Validation Failed', 'The from manufacturer field is required.'],
            'No from manufacturer' => [['to_manufacturer' => 'Testing Inventory After'], ResourceException::class, 'Validation Failed', 'The from manufacturer field is required.'],
            'No to manufacturer'   => [['from_manufacturer' => 'Testing Inventory Before'], ResourceException::class, 'Validation Failed', 'The to manufacturer field is required.'],
        ];
    }
}
