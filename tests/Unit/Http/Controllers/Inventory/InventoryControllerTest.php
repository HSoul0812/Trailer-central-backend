<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Inventory;

use App\Http\Requests\Inventory\GetInventoryHistoryRequest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\Inventory\InventoryHistorySeeder;
use Tests\TestCase;
use App\Http\Controllers\v1\Inventory\InventoryController;
use TypeError;

class InventoryControllerTest extends TestCase
{
    /**
     * @var InventoryHistorySeeder
     */
    private $seeder;

    /**
     * Tests that SUT is throwing the correct exception when some query parameter is invalid
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider invalidQueryParametersProvider
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers       InventoryController::history
     */
    public function testHistoryInvalidParameters(
        array $params,
        string $expectedException,
        string $expectedExceptionMessage,
        ?string $firstExpectedErrorMessage
    ): void
    {
        // Given I have a collection of inventory transactions
        $this->seeder->seed();

        // When I call the history action
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $request = new GetInventoryHistoryRequest($params);
        $controller = app()->make(InventoryController::class);

        try {
            $controller->history($params['inventory_id'] ?? null, $request);
        } catch (TypeError $exception) {

            self::assertStringContainsString($expectedExceptionMessage, $exception->getMessage());

            throw $exception;
        } catch (ResourceException $exception) {

            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());

            throw $exception;
        }
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new InventoryHistorySeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * Examples of invalid query parameters with their respective expected exception class name and its messages
     *
     * @return array[]
     */
    public function invalidQueryParametersProvider(): array
    {
        return [                                // array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedCustomerName
            'InventoryId must be an integer' => [[], TypeError::class, 'Argument 1 passed to App\Http\Controllers\v1\Inventory\InventoryController::history() must be of the type int, null given', null],
            'Customer must to be an integer' => [['inventory_id' => 666999, 'customer_id' => [666999]], ResourceException::class, 'Validation Failed', 'The customer id needs to be an integer.'],
            'Search term invalid'            => [['inventory_id' => 666999, 'search_term' => ['Truck']], ResourceException::class, 'Validation Failed', 'The search term must be a string.'],
            'Sort invalid'                   => [['inventory_id' => 666999, 'sort' => '-with'], ResourceException::class, 'Validation Failed', 'The selected sort is invalid.'],
            'Per page invalid (min)'         => [['inventory_id' => 666999, 'per_page' => -10], ResourceException::class, 'Validation Failed', 'The per page must be at least 1.'],
            'Per page invalid (max)'         => [['inventory_id' => 666999, 'per_page' => 5000000], ResourceException::class, 'Validation Failed', 'The per page may not be greater than 2000.']
        ];
    }
}
