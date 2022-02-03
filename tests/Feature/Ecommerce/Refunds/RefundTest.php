<?php

namespace Tests\Feature\Ecommerce\Refunds;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;
use App\Models\Parts\Textrail\Part;
use App\Models\User\AuthToken;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;
use App\Models\User\User;

abstract class RefundTest extends TestCase
{
    use WithFaker;

    protected const VERB = '';
    protected const ENDPOINT = '';
    protected const SKIP_REFUND_CREATION = false;

    protected function itShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(static::VERB, static::ENDPOINT)->assertStatus(403);
    }

    /** @var array{dealer: User, order: CompletedOrder, token: AuthToken, refund: ?Refund} */
    protected $seed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed = $this->createRefund([], static::SKIP_REFUND_CREATION);
    }

    protected function tearDown(): void
    {
        $this->tearDownSeed($this->seed['dealer']->dealer_id);

        parent::tearDown();
    }

    protected function tearDownSeed(int $dealerId): void
    {
        /** @var CompletedOrder $order */
        $order = CompletedOrder::query()->where('dealer_id', $dealerId)->first();

        foreach ($order->parts as $part) {
            Part::query()->where('id', $part['id'])->delete();
        }

        Refund::query()->where('dealer_id', $dealerId)->delete();
        CompletedOrder::query()->where('dealer_id', $dealerId)->delete();
        User::query()->where('dealer_id', $dealerId)->delete();
    }

    /**
     * @param array{order: ?array,refund: ?array} $attributes
     * @param bool $skipRefundCreation
     * @return array{dealer: User, order: CompletedOrder, token: AuthToken, refund: ?Refund}
     */
    protected function createRefund(array $attributes = [], bool $skipRefundCreation = false): array
    {
        $dealer = factory(User::class)->create();

        $token = factory(AuthToken::class)->create([
            'user_id' => $dealer->dealer_id,
            'user_type' => 'dealer',
        ]);

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
                    'total_amount' => $totalOrder,
                    'ecommerce_order_status' => CompletedOrder::ECOMMERCE_STATUS_APPROVED
                ],
                $attributes['order'] ?? []
            )
        );

        $refund = null;

        if (!$skipRefundCreation) {
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
        }

        return [
            'dealer' => $dealer,
            'order' => $order,
            'token' => $token,
            'refund' => $refund
        ];
    }
}
