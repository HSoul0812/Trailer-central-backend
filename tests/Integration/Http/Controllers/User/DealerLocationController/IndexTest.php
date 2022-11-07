<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\User\DealerLocationController;

use App\Http\Controllers\v1\User\DealerLocationController;
use App\Http\Requests\User\GetDealerLocationRequest;
use App\Models\User\DealerLocation;
use App\Repositories\User\DealerLocationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * @covers \App\Http\Controllers\v1\User\DealerLocationController::index
 * @group DealerLocations
 */
class IndexTest extends AbstractDealerLocationController
{
    /**
     * @dataProvider invalidParametersProvider
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @throws Exception when an unexpected exception has been thrown instead of the desired exception
     */
    public function testWithInvalidParameters(array $params,
                                             string $expectedException,
                                             string $expectedExceptionMessage,
                                             ?string $firstExpectedErrorMessage): void
    {
        // Given I have some invalid request parameters $params

        // And I'm using the controller "DealerLocationController"
        $controller = app(DealerLocationController::class);

        // And I have a "GetDealerLocationRequest" request using those invalids $params
        $paramsExtracted = $this->seeder->extractValues($params);
        $request = new GetDealerLocationRequest($paramsExtracted);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);
        // And I expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the "index" action using the invalid request
            $controller->index($request);
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());

            throw $exception;
        }
    }

    /**
     * @dataProvider validParametersProvider
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @param callable|string $expectedName
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWithValidParameters(array $params, int $expectedTotal, int $expectedLastPage, $expectedName): void
    {
        /** @var LengthAwarePaginator $paginator */
        /** @var DealerLocation $firstLocation */

        // Given I have some valid request parameters $params

        // And we have some dealer locations
        $this->seeder->seed();

        // And I'm using the controller "DealerLocationController"
        $controller = app(DealerLocationController::class);

        // And I have a "GetDealerLocationRequest" request using those valid $params
        $paramsExtracted = $this->seeder->extractValues($params);
        $request = new GetDealerLocationRequest($paramsExtracted);

        // When I call the "index" action using the valid request
        $response = $controller->index($request);
        $paginator = $response->original;
        $firstLocation = $paginator->first();

        // Then I should see that response status is 200
        $this->assertSame(JsonResponse::HTTP_OK, $response->status());

        // And I should see that $paginator is an expected instance type
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);

        // And I should see that expected total of dealer locations is the same as dealer locations retrieved
        $this->assertSame($expectedTotal, $paginator->total());

        // And I should see that expected last page number is same as retrieved
        $this->assertSame($expectedLastPage, $paginator->lastPage());

        // And I should see that expected name of first dealer location is the same as the first of dealer locations retrieved
        $this->assertSame(is_string($expectedName) ? $expectedName : $expectedName($this->seeder)->name, $firstLocation->name);
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function invalidParametersProvider(): array
    {
        return [                     // array $params, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No dealer'           => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Non existent dealer' => [['dealer_id' => $this->faker->numberBetween(700000, 800000)], ResourceException::class, 'Validation Failed', 'The selected dealer id is invalid.']
        ];
    }

    /**
     * Examples of valid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function validParametersProvider(): array
    {
        return [                                                       // array $params, int $expectedTotal, int $expectedLastPage, string $expectedName
            'By dummy dealer paginated by 3'                           => [['dealer_id' => $this->getSeededData(0,'dealerId'), 'per_page' => 3], 9, 3, $this->getSeededData(0,'firstLocation')],
            'By dummy dealer with search term'                         => [['dealer_id' => $this->getSeededData(0,'dealerId'), 'search_term' => 'Springfield XXX'], 1, 1, 'Springfield XXX'],
            'By dummy dealer with custom condition'                    => [['dealer_id' => $this->getSeededData(1,'dealerId'), DealerLocationRepositoryInterface::CONDITION_AND_WHERE => [['city', '=', 'Shelbyville XXX']]], 1, 1, 'Shelbyville YYY'],
            'By dummy dealer with custom condition and paginated by 2' => [['dealer_id' => $this->getSeededData(2,'dealerId'), 'per_page' => 2], 6, 3, $this->getSeededData(2,'firstLocation')]
        ];
    }
}
