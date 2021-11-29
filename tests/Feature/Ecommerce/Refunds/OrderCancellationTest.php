<?php

namespace Tests\Feature\Ecommerce\Refunds;

use App\Jobs\Ecommerce\ProcessRefundOnPaymentGatewayJob;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;
use App\Models\User\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use WithFaker;

    protected const VERB = 'POST';
    protected const ENDPOINT = '/api/ecommerce/cancellation/{textrail_order_id}';

    /** @var array{dealer: User, order: CompletedOrder} */
    protected $seed;

    public function testItShouldNotCancelTheOrderWhenTheArgumentIsWrong(): void
    {
        $textTrailOrderId = $this->faker->numberBetween(2000, 3000);

        $exceptedFieldWithError = 'textrail_order_id';
        $exceptedMessage = 'Validation Failed';

        $response = $this->json(self::VERB, str_replace('{textrail_order_id}', $textTrailOrderId, static::ENDPOINT));

        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertArrayHasKey($exceptedFieldWithError, $json['errors']);
        self::assertSame($exceptedMessage, $json['message']);
        self::assertSame(['The selected textrail order id is invalid.'], $json['errors'][$exceptedFieldWithError]);
    }

    public function testItShouldNotCancelTheOrderWhenItIsRefunded(): void
    {
        $exceptedFieldWithError = 'order';
        $exceptedMessage = 'Validation Failed';

        $otherSeed = $this->createDealerWithOrder(['refund_status' => CompletedOrder::REFUND_STATUS_REFUNDED]);

        ['order' => $order] = $otherSeed;

        $response = $this->json(self::VERB, str_replace('{textrail_order_id}', $order->ecommerce_order_id, static::ENDPOINT));

        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertArrayHasKey($exceptedFieldWithError, $json['errors']);
        self::assertSame($exceptedMessage, $json['message']);
        self::assertSame(["$order->id order is not refundable due it is refunded"], $json['errors'][$exceptedFieldWithError]);

        $this->tearDownSeed($otherSeed['dealer']->dealer_id);
    }

    public function testItShouldEnqueueJobToRefundTheOrder(): void
    {
        Bus::fake();

        ['order' => $order] = $this->seed;

        $response = $this->json(self::VERB, str_replace('{textrail_order_id}', $order->ecommerce_order_id, static::ENDPOINT));

        $response->assertStatus(202);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('response', $json);
        self::assertArrayHasKey('status', $json['response']);
        self::assertArrayHasKey('data', $json['response']);
        self::assertIsArray($json['response']['data']);
        self::assertIsNumeric($json['response']['data']['id']);

        Bus::assertDispatched(ProcessRefundOnPaymentGatewayJob::class);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seed = $this->createDealerWithOrder();
    }

    public function tearDown(): void
    {
        $this->tearDownSeed($this->seed['dealer']->dealer_id);

        parent::tearDown();
    }

    protected function tearDownSeed(int $dealerId): void
    {
        Refund::query()->where('dealer_id', $dealerId)->delete();
        CompletedOrder::query()->where('dealer_id', $dealerId)->delete();
        User::query()->where('dealer_id', $dealerId)->delete();
    }

    /**
     * @param array $attributes
     * @return array{dealer: User, order: CompletedOrder}
     */
    protected function createDealerWithOrder(array $attributes = []): array
    {
        $dealer = factory(User::class)->create();

        $order = factory(CompletedOrder::class)->create(
            array_merge(
                [
                    'dealer_id' => $dealer->dealer_id,
                    'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
                    'refund_status' => CompletedOrder::REFUND_STATUS_UNREFUNDED,
                    'payment_intent' => $this->faker->uuid,
                    'ecommerce_order_id' => $this->faker->numberBetween(1, 1000),
                ],
                $attributes
            )
        );

        return [
            'dealer' => $dealer,
            'order' => $order
        ];
    }
}
