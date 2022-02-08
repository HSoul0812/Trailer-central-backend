<?php

namespace Tests\Feature\Ecommerce\Refunds;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;
use App\Models\Parts\Textrail\Part;

class CreateReturnTest extends RefundTest
{
    protected const VERB = 'POST';
    protected const ENDPOINT = '/api/ecommerce/orders/{textrail_order_id}/returns';
    protected const SKIP_REFUND_CREATION = true;

    /**
     * @dataProvider badArgumentsProvider
     */
    public function testItShouldNotCreateReturnWhenTheArgumentsAreWrong(
        array  $parameters,
        int    $expectedHttpStatusCode,
        string $expectedMessage,
        array  $expectedErrorMessages
    ): void
    {
        $textTrailOrderId = is_callable($parameters['order']) ? $parameters['order']($this->seed)->ecommerce_order_id : $parameters['order'];

        $response = $this->json(self::VERB, str_replace('{textrail_order_id}', $textTrailOrderId, static::ENDPOINT), $parameters);

        $response->assertStatus($expectedHttpStatusCode);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertSame($expectedMessage, $json['message']);
        self::assertSame($expectedErrorMessages, $json['errors']);
    }

    public function testItShouldCreateReturnWhenArgumentsIsFine(): void
    {
        ['order' => $order] = $this->seed;

        $order->ecommerce_order_status = CompletedOrder::ECOMMERCE_STATUS_APPROVED;
        $order->save();

        $parameters = [
            'Rma' => $this->faker->numberBetween(12000, 15000),
            'Items' => collect($order->parts)->take(1)->map(function (array $part): array {
                $partModel = Part::find($part['id']);
                return ['Sku' => $partModel->sku, 'Qty' => $part['qty']];
            })->toArray()
        ];

        $response = $this->json(
            self::VERB,
            str_replace('{textrail_order_id}', $order->ecommerce_order_id, static::ENDPOINT),
            $parameters
        );

        $response->assertStatus(201);
        $json = json_decode($response->getContent(), true);
        
        self::assertArrayHasKey('response', $json);
        self::assertArrayHasKey('id', $json['response']['data']);

        $refund = Refund::find($json['response']['data']['id']);

        self::assertSame($parameters['Rma'], $refund->textrail_rma);
    }

    public function badArgumentsProvider(): array
    {
        $this->refreshApplication();
        $this->setUpTraits();

        $getOrder = static function (array $seed): CompletedOrder {
            return $seed['order'];
        };

        return [
            'Non existent order, no items' => [
                ['order' => $this->faker->numberBetween(12000, 15000)],
                422,
                'Validation Failed',
                [
                    'textrail_order_id' => ['The selected textrail order id is invalid.'],
                    'Rma' => ['The rma field is required.'],
                    'Items' => ['The items field is required.']
                ]
            ],
            'Wrong order, bad parts' => [
                ['order' => $getOrder, 'Rma' => 'Wrong number', 'Items' => 'Wrong status'],
                422,
                'Validation Failed',
                [
                    'Rma' => ['The rma needs to be an integer.'],
                    'Items' => ['The items needs to be an array.']
                ]
            ],
            'Item SKU is required' => [
                ['order' => $getOrder, 'Rma' => $this->faker->numberBetween(300, 8000), 'Items' => [['Qty' => $this->faker->numberBetween(3, 8)]]],
                422,
                'Validation Failed',
                [
                    'Items.0.Sku' => ['The Items.0.Sku field is required.']
                ]
            ],
            'There is not any product with the provided SKU' => [
                ['order' => $getOrder, 'Rma' => $this->faker->numberBetween(300, 8000), 'Items' => [['Sku' => $this->faker->numberBetween(3, 8) . 'TTT', 'Qty' => $this->faker->numberBetween(3, 8)]]],
                422,
                'Validation Failed',
                [
                    'Items.0.Sku' => ['The selected Items.0.Sku is invalid.']
                ]
            ],
            'There is not any product with the provided SKU and Item qty is required' => [
                ['order' => $getOrder, 'Rma' => $this->faker->numberBetween(300, 8000), 'Items' => [['Sku' => $this->faker->numberBetween(3, 8) . 'TTT']]],
                422,
                'Validation Failed',
                [
                    'Items.0.Sku' => ['The selected Items.0.Sku is invalid.'],
                    'Items.0.Qty' => ['The Items.0.Qty field is required.']
                ]
            ],
            'There is not any product with the provided SKU and Item qty is wrong' => [
                ['order' => $getOrder, 'Rma' => $this->faker->numberBetween(300, 8000), 'Items' => [['Sku' => $this->faker->numberBetween(3, 8) . 'TTT', 'Qty' => 'wrong type']]],
                422,
                'Validation Failed',
                [
                    'Items.0.Sku' => ['The selected Items.0.Sku is invalid.'],
                    'Items.0.Qty' => ['The Items.0.Qty needs to be an integer.']
                ]
            ]
        ];
    }
}
