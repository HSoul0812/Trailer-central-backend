<?php

namespace Tests\Feature\Ecommerce\Refunds;

use App\Jobs\Ecommerce\ProcessRefundOnPaymentGatewayJob;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;
use App\Models\Ecommerce\RefundTextrailStatuses;
use App\Models\Parts\Textrail\Part;
use App\Models\User\User;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class UpdateReturnStatusTest extends TestCase
{
    use WithFaker;

    protected const VERB = 'POST';
    protected const ENDPOINT = '/api/ecommerce/returns/{rma}';

    /** @var array{dealer: User, order: CompletedOrder} */
    protected $seed;

    /**
     * @dataProvider badArgumentsProvider
     */
    public function testItShouldNotUpdateTheRefundWhenTheArgumentsAreWrong(
        array  $parameters,
        int    $expectedHttpStatusCode,
        string $expectedMessage,
        array  $expectedErrorMessages
    ): void
    {
        $rma = is_callable($parameters['Rma']) ? $parameters['Rma']($this->seed) : $parameters['Rma'];

        $response = $this->json(
            self::VERB, str_replace('{rma}', $rma, static::ENDPOINT),
            $parameters
        );

        $response->assertStatus($expectedHttpStatusCode);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertSame($expectedMessage, $json['message']);
        self::assertSame($expectedErrorMessages, $json['errors']);
    }

    public function testItShouldNotUpdateTheRefundDueTheStatusIsWrong(): void
    {
        // Refund on authorized status, trying to update to an invalid status
        // 1) authorized -> authorized
        /** @var Refund $refund */
        $refund = $this->seed['refund'];

        $parts = collect($refund->parts)->map(function (array $part) {
            return ['Sku' => $part['sku'], 'Qty' => $part['qty']];
        })->toArray();

        $body = [
            'Status' => RefundTextrailStatuses::RECEIVED,
            'Items' => $parts
        ];

        $response = $this->json(
            self::VERB, str_replace('{rma}', $refund->textrail_rma, static::ENDPOINT),
            $body
        );

        $this->assertResponseHasValidationError($response, "Refund status cannot move from 'pending' to 'return_received'");

        // 1) authorized -> authorized
        $refund->status = Refund::STATUS_AUTHORIZED;
        $refund->save();

        $body = [
            'Status' => RefundTextrailStatuses::AUTHORIZED,
            'Items' => $parts
        ];

        $response = $this->json(
            self::VERB, str_replace('{rma}', $refund->textrail_rma, static::ENDPOINT),
            $body
        );

        $this->assertResponseHasValidationError($response, "Refund status cannot move from 'authorized' to 'authorized'");
    }

    public function testItShouldNotUpdateTheRefundDueWrongItem(): void
    {
        /** @var Refund $refund */
        $refund = $this->seed['refund'];

        $wrongSku = $this->faker->numberBetween(600, 9000);

        // some wrong sku part
        $body = [
            'Status' => RefundTextrailStatuses::AUTHORIZED,
            'Items' => collect($refund->parts)->map(function (array $part) use ($wrongSku) {
                return ['Sku' => $wrongSku, 'Qty' => $part['qty']];
            })->toArray()
        ];

        $response = $this->json(
            self::VERB, str_replace('{rma}', $refund->textrail_rma, static::ENDPOINT),
            $body
        );

        $this->assertResponseHasValidationError($response, sprintf('"%s" part was not originally requested to be refunded', $wrongSku));

        // some wrong qty part
        $body = [
            'Status' => RefundTextrailStatuses::AUTHORIZED,
            'Items' => collect($refund->parts)->map(function (array $part) {
                return ['Sku' => $part['sku'], 'Qty' => $part['qty'] + 2];
            })->toArray()
        ];

        $response = $this->json(
            self::VERB, str_replace('{rma}', $refund->textrail_rma, static::ENDPOINT),
            $body
        );

        $this->assertResponseHasValidationError(
            $response,
            sprintf('"%s" part must be in the range of 0 and %d', $refund->parts[0]['sku'], $refund->parts[0]['qty'])
        );
    }

    public function testItShouldMarkTheRefundAsRejected(): void
    {
        /** @var Refund $refund */
        $refund = $this->seed['refund'];
        /** @var CompletedOrder $order */
        $order = $this->seed['order'];

        $body = [
            'Status' => RefundTextrailStatuses::DENIED,
            'Items' => collect($refund->parts)->map(function (array $part) {
                return ['Sku' => $part['sku'], 'Qty' => $part['qty']];
            })->toArray()
        ];

        $response = $this->json(
            self::VERB, str_replace('{rma}', $refund->textrail_rma, static::ENDPOINT),
            $body
        );

        $response->assertStatus(202);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('response', $json);
        self::assertArrayHasKey('status', $json['response']);
        self::assertArrayHasKey('data', $json['response']);
        self::assertArrayHasKey('id', $json['response']['data']);

        /** @var CompletedOrder $updatedOrder */
        $updatedOrder = CompletedOrder::query()->where('id', $order->id)->first();

        self::assertSame(0.0, (float)$updatedOrder->total_refunded_amount);
    }

    public function testItShouldMarkTheRefundAsAuthorized(): void
    {
        /** @var Refund $refund */
        $refund = $this->seed['refund'];
        /** @var CompletedOrder $order */
        $order = $this->seed['order'];

        $body = [
            'Status' => RefundTextrailStatuses::AUTHORIZED,
            'Items' => collect($refund->parts)->map(function (array $part) {
                return ['Sku' => $part['sku'], 'Qty' => $part['qty'] - 1]; // we will authorize the requested qty minus one
            })->toArray()
        ];

        $response = $this->json(
            self::VERB, str_replace('{rma}', $refund->textrail_rma, static::ENDPOINT),
            $body
        );

        $response->assertStatus(202);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('response', $json);
        self::assertArrayHasKey('status', $json['response']);
        self::assertArrayHasKey('data', $json['response']);
        self::assertArrayHasKey('id', $json['response']['data']);

        /** @var CompletedOrder $updatedOrder */
        $updatedOrder = CompletedOrder::query()->where('id', $order->id)->first();

        self::assertGreaterThan((float)$updatedOrder->total_refunded_amount, $order->total_refunded_amount);
    }

    public function testItShouldMarkTheRefundAsReceived(): void
    {
        /** @var Refund $refund */
        $refund = $this->seed['refund'];
        $refund->status = Refund::STATUS_AUTHORIZED;
        $refund->save();

        /** @var CompletedOrder $order */
        $order = $this->seed['order'];

        $body = [
            'Status' => RefundTextrailStatuses::RECEIVED,
            'Items' => collect($refund->parts)->map(function (array $part) {
                return ['Sku' => $part['sku'], 'Qty' => $part['qty']];
            })->toArray()
        ];

        Bus::fake();

        $response = $this->json(
            self::VERB, str_replace('{rma}', $refund->textrail_rma, static::ENDPOINT),
            $body
        );

        $response->assertStatus(202);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('response', $json);
        self::assertArrayHasKey('status', $json['response']);
        self::assertArrayHasKey('data', $json['response']);
        self::assertArrayHasKey('id', $json['response']['data']);


        /** @var CompletedOrder $updatedOrder */
        $updatedOrder = CompletedOrder::query()->where('id', $order->id)->first();

        self::assertSame((float)$updatedOrder->total_refunded_amount, $order->total_refunded_amount);
        Bus::assertDispatched(ProcessRefundOnPaymentGatewayJob::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed = $this->seed ?? $this->createRefund();
    }

    protected function tearDown(): void
    {
        $this->tearDownSeed($this->seed['dealer']->dealer_id);

        parent::tearDown();
    }

    protected function tearDownSeed(int $dealerId): void
    {
        /** @var Refund $refund */
        $refund = Refund::query()->where('dealer_id', $dealerId)->first();

        foreach ($refund->parts as $part) {
            Part::query()->where('sku', $part['sku'])->delete();
        }

        Refund::query()->where('dealer_id', $dealerId)->delete();
        CompletedOrder::query()->where('dealer_id', $dealerId)->delete();
        User::query()->where('dealer_id', $dealerId)->delete();
    }

    public function badArgumentsProvider(): array
    {
        $this->refreshApplication();
        $this->setUpFaker();

        $getRefund = static function (array $seed): int {
            return $seed['refund']->textrail_rma;
        };

        $getFakeRma = function (array $seed): int {
            return $this->faker->numberBetween(12000, 15000);
        };

        return [
            'Non existent rma, no status, and not items' => [
                ['Rma' => $getFakeRma],
                422,
                'Validation Failed',
                [
                    'Rma' => ['The selected rma is invalid.'],
                    'Status' => ['The status field is required.'],
                    'Items' => ['The items field is required.']
                ]
            ],
            'Bad status, and not items' => [
                ['Rma' => $getRefund, 'Status' => 'Wrong status'],
                422,
                'Validation Failed',
                [
                    'Status' => ['The selected status is invalid.'],
                    'Items' => ['The items field is required.']
                ]
            ],
            'Items as string' => [
                ['Rma' => $getRefund, 'Status' => RefundTextrailStatuses::AUTHORIZED, 'Items' => 'Wrong items'],
                422,
                'Validation Failed',
                [
                    'Items' => ['The items needs to be an array.']
                ]
            ],
            'Sku qty is required' => [
                ['Rma' => $getRefund, 'Status' => RefundTextrailStatuses::AUTHORIZED, 'Items' => [['Qty' => $this->faker->numberBetween(3, 8)]]],
                422,
                'Validation Failed',
                [
                    'Items.0.Sku' => ['The Items.0.Sku field is required.']
                ]
            ],
            'Item qty is required' => [
                ['Rma' => $getRefund, 'Status' => RefundTextrailStatuses::AUTHORIZED, 'Items' => [['Sku' => $this->faker->word]]],
                422,
                'Validation Failed',
                [
                    'Items.0.Qty' => ['The Items.0.Qty field is required.']
                ]
            ],
            'Item qty is wrong' => [
                ['Rma' => $getRefund, 'Status' => RefundTextrailStatuses::AUTHORIZED, 'Items' => [['Sku' => $this->faker->word, 'Qty' => 'Hello']]],
                422,
                'Validation Failed',
                [
                    'Items.0.Qty' => ['The Items.0.Qty needs to be an integer.']
                ]
            ]
        ];
    }


    /**
     * @param array{order: ?array,refund: ?array} $attributes
     * @return array{dealer: User, order: CompletedOrder}
     */
    protected function createRefund(array $attributes = []): array
    {
        $dealer = factory(User::class)->create();

        $totalOrder = 0;

        $partModels = factory(Part::class, 2)->create([
            'manufacturer_id' => 66,
            'brand_id' => 25,
            'type_id' => 11,
            'category_id' => 8
        ])->keyBy('id');

        $parts = [];
        $textrailItems = [];

        foreach ($partModels as $part) {
            $qty = $this->faker->numberBetween(2, 4);
            $totalOrder += $qty * $part->price;

            $parts[] = ['id' => $part->id, 'qty' => $qty, 'price' => $part->price];
            $textrailItems[] = [
                'item_id' => $this->faker->numberBetween(3, 800),
                'sku' => $part->sku,
                'name' => $part->title,
                'qty' => $qty,
                'price' => $part->price,
                'product_type' => 'simple',
                'quote_id' => $this->faker->numberBetween(300, 3000)
            ];
        }

        $order = factory(CompletedOrder::class)->create(
            array_merge(
                [
                    'dealer_id' => $dealer->dealer_id,
                    'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
                    'refund_status' => CompletedOrder::REFUND_STATUS_PARTIAL_REFUNDED,
                    'payment_intent' => $this->faker->uuid,
                    'ecommerce_order_id' => $this->faker->numberBetween(1, 1000),
                    'ecommerce_order_code' => $this->faker->numberBetween(1, 1000),
                    'ecommerce_cart_id' => $this->faker->uuid,
                    'parts' => $parts,
                    'ecommerce_items' => $textrailItems,
                    'total_amount' => $totalOrder
                ],
                $attributes['order'] ?? []
            )
        );

        $total = 0;
        $parts = [];

        foreach (collect($order->parts) as $part) {
            $subTotal = $part['qty'] * $part['price'];
            $total += $subTotal;
            $parts[] = array_merge($part, ['name' => $partModels[$part['id']]->title, 'sku' => $partModels[$part['id']]->sku, 'amount' => $subTotal]);
        }

        $refund = factory(Refund::class)->create(
            array_merge(
                [
                    'dealer_id' => $dealer->dealer_id,
                    'order_id' => $order->id,
                    'parts' => $parts,
                    'parts_amount' => $total,
                    'total_amount' => $total,
                    'textrail_rma' => $this->faker->numberBetween(1, 1000)
                ],
                $attributes['refund'] ?? []
            )
        );

        $order->refund_status = CompletedOrder::REFUND_STATUS_REFUNDED;
        $order->refunded_at = Date::now();
        $order->refunded_parts = $parts;
        $order->parts_refunded_amount = $total;
        $order->total_refunded_amount = $total;
        $order->save();

        return [
            'dealer' => $dealer,
            'order' => $order,
            'refund' => $refund,
        ];
    }

   /* public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->refreshApplication();
        $this->setUpTraits();
    }*/

    private function assertResponseHasValidationError(TestResponse $response, string $message): void
    {
        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertSame('Validation Failed', $json['message']);
        self::assertSame($message, $json['errors']['refund'][0]);
    }
}
