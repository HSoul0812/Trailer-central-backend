<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Dms\Customer;

use App\Http\Requests\Dms\Customer\GetInventoryRequest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\Dms\Customer\InventorySeeder;
use Tests\TestCase;
use App\Http\Controllers\v1\Dms\Customer\InventoryController;

class InventoryControllerTest extends TestCase
{
    /**
     * @var InventorySeeder
     */
    private $seeder;

    /**
     * @dataProvider queryBadRequestedParametersAndSummariesProvider
     *
     * @param  array  $params
     * @param  string  $expectedException
     * @param  string  $expectedExceptionMessage
     * @param  string|null  $firstExpectedErrorMessage
     * @note IntegrationTestCase
     * @throws BindingResolutionException
     */
    public function testListIsThrowingExceptionsAsExpectedWhenThereAreInvalidParameters(
        array $params,
        string $expectedException,
        string $expectedExceptionMessage,
        ?string $firstExpectedErrorMessage
    ): void {
        $this->seeder->seed();

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $request = new GetInventoryRequest($params);
        $controller = app()->make(InventoryController::class);

        try {
            $controller->index($request);
        } catch (ResourceException $exception) {

            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());

            throw $exception;
        }
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new InventorySeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * Examples of parameters with expected exception and its messages
     *
     * @return array[]
     */
    public function queryBadRequestedParametersAndSummariesProvider(): array
    {
        return [                                      // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'Dealer is required'            => [[], ResourceException::class, 'Validation Failed','The dealer id field is required.'],
            'Customer is required'          => [['dealer_id' => 666999], ResourceException::class, 'Validation Failed','The customer id field is required.'],
            'Customer must to be and array' => [['dealer_id' => 666999, 'customer_id' => 666999], ResourceException::class, 'Validation Failed','The customer id needs to be an array.'],
            'Sort invalid'                  => [['dealer_id' => 666999, 'customer_id' => [666999], 'sort' =>'-with'], ResourceException::class, 'Validation Failed', 'The selected sort is invalid.'],
            'Per page invalid (min)'        => [['dealer_id' => 666999, 'customer_id' => [666999], 'per_page' => -10], ResourceException::class, 'Validation Failed', 'The per page must be at least 1.'],
            'Per page invalid (max)'        => [['dealer_id' => 666999, 'customer_id' => [666999], 'per_page' => 5000000], ResourceException::class, 'Validation Failed', 'The per page may not be greater than 2000.'],
            'Search term invalid'           => [['dealer_id' => 666999, 'customer_id' => [666999], 'search_term' => ['Truck']], ResourceException::class, 'Validation Failed', 'The search term must be a string.'],
            'Customer condition invalid'    => [['dealer_id' => 666999, 'customer_id' => [666999], 'customer_condition' => '-asc'], ResourceException::class, 'Validation Failed', 'The selected customer condition is invalid.']
        ];
    }
}
