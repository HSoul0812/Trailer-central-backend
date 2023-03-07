<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\User\DealerLocationController;

use App\Http\Controllers\v1\User\DealerLocationController;
use App\Http\Requests\User\DeleteDealerLocationRequest;
use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;
use Exception;

/**
 * @covers \App\Http\Controllers\v1\User\DealerLocationController::destroy
 * @group DealerLocations
 */
class DestroyTest extends AbstractDealerLocationController
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
     * @param string|array|callable $expectedErrorMessages
     *
     * @throws Exception when an unexpected exception has been thrown instead of the desired exception
     */
    public function testWithInvalidParameter(array $params,
                                             string $expectedException,
                                             string $expectedExceptionMessage,
                                             $expectedErrorMessages): void
    {
        // Given I have some invalid request parameters $params

        // And I know there are some dealers and locations
        $this->seeder->seed();

        // And I'm using the controller "DealerLocationController"
        $controller = app(DealerLocationController::class);

        // And I have a "DeleteDealerLocationRequest" request using those invalids $params
        $paramsExtracted = $this->seeder->extractValues($params);
        $locationId = (int)($paramsExtracted['id'] ?? null);

        $request = new DeleteDealerLocationRequest($paramsExtracted);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);

        // And I expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the "destroy" action using the invalid request
            $controller->destroy($locationId, $request);
        } catch (ResourceException $exception) {
            if (is_string($expectedErrorMessages)) {
                // Then I should see that the first error message has a specific string
                self::assertSame($expectedErrorMessages, $exception->getErrors()->first());
            } else if (is_array($expectedErrorMessages)) {
                // Then I should see that the error collection has all expected fields with errors
                $fieldsWithErrors = $exception->getErrors()->keys();
                foreach ($expectedErrorMessages as $Key) {
                    self::assertContainsEquals($Key, $fieldsWithErrors);
                }
            } else {
                // Then I should see that the error collection has some other error
                $expectedErrorMessages($exception->getErrors());
            }

            throw $exception;
        }
    }

    /**
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWithValidParameter(): void
    {
        /** @var array $data */

        // Given we have some dealer locations
        $this->seeder->seed();

        // And I'm using the controller "DealerLocationController"
        $controller = app(DealerLocationController::class);

        // And I have a dealer id
        $dealerId = $this->seeder->dealers[0]->dealer_id;

        // And I have a dealer location id
        $locationId = $this->seeder->locations[$dealerId]->first()->dealer_location_id;

        // And I have a dealer location id to move the references
        $targetLocationId = $this->seeder->locations[$dealerId]->first()->dealer_location_id;

        // And I have a "UpdateDealerLocationRequest" request using those valid $params
        $params = [
            'dealer_id' => $dealerId,
            'id' => $locationId,
            'move_references_to_location_id' => $targetLocationId
        ];
        $request = new DeleteDealerLocationRequest($params);

        // When I call the "destroy" action using the valid request
        $response = $controller->destroy($locationId, $request);

        // Then I should see that response status is 204
        self::assertSame(JsonResponse::HTTP_NO_CONTENT, $response->status());
        self::assertEmpty($response->content());

        // And I should see that a record has been soft deleted
        $this->assertSoftDeleted(DealerLocation::getTableName(), ['dealer_location_id' => $locationId]);

        // And I should see the target dealer location has some related records
        self::assertCount(5, Inventory::where(['dealer_location_id' => $targetLocationId])->get());
        self::assertCount(3, ApiEntityReference::where([
            'entity_type' => ApiEntityReference::TYPE_LOCATION,
            'entity_id' => $targetLocationId
        ])->get());
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function invalidParametersProvider(): array
    {
        $otherAssertions = $this->errorsAssertions();

        return [                                                          // array $params, string $expectedException, string $expectedExceptionMessage, string|array $firstExpectedErrorMessage
            'No dealer'                                                   => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Non existent dealer'                                         => [['dealer_id' => $this->faker->numberBetween(700000, 800000)], ResourceException::class, 'Validation Failed', 'The selected dealer id is invalid.'],
            'No dealer location'                                          => [['dealer_id' => $this->getSeededData(0, 'dealerId')], ResourceException::class, 'Validation Failed', $otherAssertions['wrong dealer location']],
            'Non existent dealer location'                                => [['dealer_id' => $this->getSeededData(0, 'dealerId'), 'id' => $this->faker->numberBetween(700000, 800000)], ResourceException::class, 'Validation Failed', $otherAssertions['wrong dealer location']],
            "A dealer location which doesn't belong to the dealer"        => [['dealer_id' => $this->getSeededData(0, 'dealerId'), 'id' => $this->getSeededData(1, 'firstLocationId')], ResourceException::class, 'Validation Failed', $otherAssertions['wrong dealer location']],
            'Non existent target dealer location'                         => [['dealer_id' => $this->getSeededData(0, 'dealerId'), 'id' => $this->getSeededData(0, 'firstLocationId'), 'move_references_to_location_id' => $this->faker->numberBetween(700000, 800000)], ResourceException::class, 'Validation Failed', $otherAssertions['wrong dealer location to move']],
            "A target dealer location which doesn't belong to the dealer" => [['dealer_id' => $this->getSeededData(0, 'dealerId'), 'id' => $this->getSeededData(0, 'firstLocationId'), 'move_references_to_location_id' => $this->getSeededData(1, 'firstLocationId')], ResourceException::class, 'Validation Failed', $otherAssertions['wrong dealer location to move']],
        ];
    }

    /**
     * @return array<string, callable>
     */
    private function errorsAssertions(): array
    {
        return [
            'wrong dealer location' => function (MessageBag $bag) {

                $error = $bag->get('id') ? $bag->get('id')[0] : '';

                self::assertSame('The selected id is invalid.', $error);
            },
            'wrong dealer location to move' => function (MessageBag $bag) {

                $error = $bag->get('move_references_to_location_id') ? $bag->get('move_references_to_location_id')[0] : '';

                self::assertSame('The selected move references to location id is invalid.', $error);
            },
        ];
    }
}
