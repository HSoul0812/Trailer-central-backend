<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\User\DealerLocationController;

use App\Http\Controllers\v1\User\DealerLocationController;
use App\Http\Requests\User\SaveDealerLocationRequest;
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
 * @covers \App\Http\Controllers\v1\User\DealerLocationController::create
 * @group DealerLocations
 */
class CreateTest extends AbstractDealerLocationController
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
    public function testWithInvalidParameter(
        array $params,
        string $expectedException,
        string $expectedExceptionMessage,
        $expectedErrorMessages
    ): void
    {
        // And I know there are some dealers and locations
        $this->seeder->seed();

        $controller = app(DealerLocationController::class);

        $paramsExtracted = $this->seeder->extractValues($params);

        // And I have a "SaveDealerLocationRequest" request using those invalids $params
        $request = new SaveDealerLocationRequest($paramsExtracted);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);
        // And I expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the "create" action using the invalid request
            $controller->create($request);
        } catch (ResourceException $exception) {
            if (is_string($expectedErrorMessages)) {
                // Then I should see that the first error message has a specific string
                $this->assertSame($expectedErrorMessages, $exception->getErrors()->first());
            } else if (is_array($expectedErrorMessages)) {
                // Then I should see that the error collection has all expected fields with errors
                $fieldsWithErrors = $exception->getErrors()->keys();
                foreach ($expectedErrorMessages as $Key) {
                    $this->assertContainsEquals($Key, $fieldsWithErrors);
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

        // And I have a "SaveDealerLocationRequest" request using those valid $params
        $params = [
            'dealer_id' => $dealerId,
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
            'is_default' => 1,
            'is_default_for_invoice' => 1,
            'sales_tax_items' => [
                ['entity_type_id' => 5, 'item_type' => 'state'],
                ['entity_type_id' => 5, 'item_type' => 'city'],
            ],
            'fees' => [
                ['title' => 'Docs fee', 'fee_type' => 'docs_fee', 'amount' => 35, 'visibility' => 'visible_locked','accounting_class' => 'Adt Default Fees'],
                ['title' => 'License fee', 'fee_type' => 'license_fee', 'amount' => 15, 'visibility' => 'visible_locked_pos','accounting_class' => 'Taxes & Fees Group 1'],
                ['title' => 'Bank fee', 'fee_type' => 'bank_fee', 'amount' => 25, 'visibility' =>  'visible_locked','accounting_class' => 'Taxes & Fees Group 3']
            ],
        ];
        $request = new SaveDealerLocationRequest($params);

        // When I call the "create" action using the valid request
        $response = $controller->create($request);
        $data = $response->original;

        // Then I should see that response status is 200
        $this->assertSame(JsonResponse::HTTP_OK, $response->status());

        // And I should see that response has a key-value "data"
        $this->assertArrayHasKey('data', $data);

        // And I should see the data retrieved has a key-value "id" which is the identifier of the recently created dealer location
        $locationId = $data['data']['id'];

        // And I should see that a record has been persisted with certain values
        $this->assertDatabaseHas(DealerLocation::getTableName(), [
            'dealer_location_id' => $locationId,
            'name' => $params['name'],
        ]);

        // And I should see that I have only one dealer location as default location and that location is the expected location
        $defaultLocations = DealerLocation::where(['dealer_id' => $dealerId, 'is_default' => 1]);
        $this->assertSame(1, $defaultLocations->count());
        $this->assertSame($locationId, $defaultLocations->first()->dealer_location_id);

        // And I should see that I have only one dealer location as default location for invoicing and that location is the expected location
        $defaultLocationsForInvoicing = DealerLocation::where(['dealer_id' => $dealerId, 'is_default_for_invoice' => 1]);
        $this->assertSame(1, $defaultLocationsForInvoicing->count());
        $this->assertSame($locationId, $defaultLocationsForInvoicing->first()->dealer_location_id);

        // And I should see the persisted record has other related records
        $this->assertSame(1, DealerLocationSalesTax::where(['dealer_location_id' => $locationId])->count());
        $this->assertSame(2, DealerLocationSalesTaxItem::where(['dealer_location_id' => $locationId])->count());
        $this->assertSame(3, DealerLocationQuoteFee::where(['dealer_location_id' => $locationId])->count());
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function invalidParametersProvider(): array
    {
        $otherAssertions = $this->errorsAssertions();

        return [                                          // array $params, string $expectedException, string $expectedExceptionMessage, string|array $firstExpectedErrorMessage
            'No dealer'                                   => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Non existent dealer'                         => [['dealer_id' => $this->faker->numberBetween(700000, 800000)], ResourceException::class, 'Validation Failed', 'The selected dealer id is invalid.'],
            "Dealer location isn't unique"                => [['dealer_id' => $this->getSeededData(0,'dealerId'), 'name' => $this->getSeededData(0,'firstLocationName')], ResourceException::class, 'Validation Failed', 'Dealer Location must be unique'],
            'No others required parameters'               => [['dealer_id' => $this->getSeededData(0,'dealerId')], ResourceException::class, 'Validation Failed', ['name', 'contact', 'address', 'city', 'county', 'region', 'country', 'postalcode', 'phone']],
            '"sales_tax_items" and "fees" are not arrays' => [['dealer_id' => $this->getSeededData(0,'dealerId'), 'sales_tax_items' => true, 'fees' => 'back_fee'], ResourceException::class, 'Validation Failed', $otherAssertions['"sales_tax_items" and "fees" errors have a specific message']],
            // @todo since there are plenty of possible errors we should add more test cases, but right now I'm not sure about theirs business value, so this is what it is
        ];
    }

    /**
     * @return array<string, callable>
     */
    private function errorsAssertions(): array
    {
        return [
            '"sales_tax_items" and "fees" errors have a specific message' => function (MessageBag $bag) {

                $salesTaxItemsError = $bag->get('sales_tax_items') ? $bag->get('sales_tax_items')[0] : '';
                $feesError = $bag->get('fees') ? $bag->get('fees')[0] : '';

                $this->assertSame('The sales tax items needs to be an array.', $salesTaxItemsError);
                $this->assertSame('The fees needs to be an array.', $feesError);
            }
        ];
    }
}
