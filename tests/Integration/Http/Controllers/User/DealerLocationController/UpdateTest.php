<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\User\DealerLocationController;

use App\Http\Controllers\v1\User\DealerLocationController;
use App\Http\Requests\User\UpdateDealerLocationRequest;
use App\Models\User\DealerLocation;
use App\Models\User\DealerLocationQuoteFee;
use App\Models\User\DealerLocationSalesTax;
use App\Models\User\DealerLocationSalesTaxItem;
use App\Models\User\DealerLocationSalesTaxItemV1;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;
use Exception;

/**
 * @covers \App\Http\Controllers\v1\User\DealerLocationController::update
 * @group DealerLocations
 */
class UpdateTest extends AbstractDealerLocationController
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

        // And I have a "UpdateDealerLocationRequest" request using those invalids $params
        $paramsExtracted = $this->seeder->extractValues($params);
        $locationId = (int)($paramsExtracted['id'] ?? null);

        $request = new UpdateDealerLocationRequest($paramsExtracted);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);

        // And I expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the "update" action using the invalid request
            $controller->update($locationId, $request);
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

        // And I have a "UpdateDealerLocationRequest" request using those valid $params
        $params = [
            'dealer_id' => $dealerId,
            'id' => $locationId,
            'name' => 'Albuquerque',
            'contact' => 'Walter White',
            'address' => '308 Negra Arroyo Lane',
            'city' => 'Albuquerque',
            'county' => 'Albuquerque',
            'region' => 'New Mexico',
            'country' => 'US',
            'postalcode' => '87104',
            'phone' => '8126295574',
            'tax_calculator_id' => 1,
            'env_fee_basis' => 'parts_and_labor',
            'env_fee_pct' => 3,
            'is_default' => 1,
            'is_default_for_invoice' => 1,
            'sales_tax_items' => [
                ['entity_type_id' => 5, 'item_type' => 'state'],
                ['entity_type_id' => 5, 'item_type' => 'city'],
            ],
            'fees' => [
                ['title' => 'Docs fee', 'fee_type' => 'docs_fee', 'amount' => 35, 'visibility' => 'visible_locked', 'accounting_class' => 'Adt Default Fees'],
                ['title' => 'License fee', 'fee_type' => 'license_fee', 'amount' => 15, 'visibility' => 'visible_locked_pos', 'accounting_class' => 'Taxes & Fees Group 1'],
                ['title' => 'Bank fee', 'fee_type' => 'bank_fee', 'amount' => 25, 'visibility' => 'visible_locked', 'accounting_class' => 'Taxes & Fees Group 3']
            ],
        ];
        $request = new UpdateDealerLocationRequest($params);

        // When I call the "update" action using the valid request
        $response = $controller->update($locationId, $request);
        $data = $response->original;

        // Then I should see that response status is 200
        $this->assertSame(JsonResponse::HTTP_OK, $response->status());

        // And I should see that response has a key-value "data"
        $this->assertArrayHasKey('data', $data);

        // And I should see the data retrieved has a key-value "id" which is the identifier of the recently updated dealer location
        $locationId = $data['data']['id'];

        // And I should see that I have only one dealer location as default location and that location is the expected location
        $defaultLocations = DealerLocation::where(['dealer_id' => $dealerId, 'is_default' => 1]);
        $this->assertSame(1, $defaultLocations->count());
        $this->assertSame($locationId, $defaultLocations->first()->dealer_location_id);

        // And I should see that I have only one dealer location as default location for invoicing and that location is the expected location
        $defaultLocationsForInvoicing = DealerLocation::where(['dealer_id' => $dealerId, 'is_default_for_invoice' => 1]);
        $this->assertSame(1, $defaultLocationsForInvoicing->count());
        $this->assertSame($locationId, $defaultLocationsForInvoicing->first()->dealer_location_id);

        // And I should see that a record has been persisted with certain values
        $this->assertDatabaseHas(DealerLocation::getTableName(), [
            'dealer_location_id' => $locationId,
            'name' => $params['name'],
            'is_default' => 1
        ]);

        $this->assertDatabaseHas(DealerLocationSalesTax::getTableName(), [
            'dealer_location_id' => $locationId,
            'env_fee_basis' => 'parts_and_labor',
            'env_fee_pct' => 3
        ]);

        // And I should see the persisted record has other related records
        $this->assertSame(1, DealerLocationSalesTax::where(['dealer_location_id' => $locationId])->count());
        $this->assertSame(2, DealerLocationSalesTaxItem::where(['dealer_location_id' => $locationId])->count());
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function invalidParametersProvider(): array
    {
        $otherAssertions = $this->errorsAssertions();

        return [                                                    // array $params, string $expectedException, string $expectedExceptionMessage, string|array $firstExpectedErrorMessage
            'No dealer'                                             => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Non existent dealer'                                   => [['dealer_id' => $this->faker->numberBetween(700000, 800000)], ResourceException::class, 'Validation Failed', 'The selected dealer id is invalid.'],
            'No dealer location'                                    => [['dealer_id' => $this->getSeededData(0, 'dealerId')], ResourceException::class, 'Validation Failed', $otherAssertions['wrong dealer location']],
            'Non existent dealer location'                          => [['dealer_id' => $this->getSeededData(0, 'dealerId'), 'id' => $this->faker->numberBetween(700000, 800000)], ResourceException::class, 'Validation Failed', $otherAssertions['wrong dealer location']],
            "Dealer location isn't unique"                => [['dealer_id' => $this->getSeededData(0,'dealerId'), 'name' => $this->getSeededData(0,'firstLocationName')], ResourceException::class, 'Validation Failed', 'Dealer Location must be unique'],
            "A dealer location which doesn't belong to the dealer"  => [['dealer_id' => $this->getSeededData(0, 'dealerId'), 'id' => $this->getSeededData(1, 'firstLocationId')], ResourceException::class, 'Validation Failed', $otherAssertions['wrong dealer location']],
            'No others required parameters'                         => [['dealer_id' => $this->getSeededData(0,'dealerId'), 'id' => $this->getSeededData(0, 'firstLocationId')], ResourceException::class, 'Validation Failed', ['name', 'contact', 'address', 'city', 'county', 'region', 'country', 'postalcode', 'phone']],
            '"sales_tax_items" and "fees" are not arrays'           => [['dealer_id' => $this->getSeededData(0,'dealerId'), 'id' => $this->getSeededData(0, 'firstLocationId'), 'sales_tax_items' => true, 'fees' => 'back_fee'], ResourceException::class, 'Validation Failed', $otherAssertions['"sales_tax_items" and "fees" errors have a specific message']],
            // @todo since there are plenty of possible errors we should add more test cases, but right now I'm not sure about theirs business value, so this is what it is
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
            '"sales_tax_items" and "fees" errors have a specific message' => function (MessageBag $bag) {

                $salesTaxItemsError = $bag->get('sales_tax_items') ? $bag->get('sales_tax_items')[0] : '';
                $feesError = $bag->get('fees') ? $bag->get('fees')[0] : '';

                self::assertSame('The sales tax items needs to be an array.', $salesTaxItemsError);
                self::assertSame('The fees needs to be an array.', $feesError);
            }
        ];
    }
}
