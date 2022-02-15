<?php

namespace Tests\Feature\Ecommerce\CompletedOrder;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use Tests\TestCase;

class ApproveOrderTest extends TestCase
{
    /** @var CompletedOrder */
    private $completedOrder;

    public function setUp(): void
    {
        parent::setUp();

        $this->completedOrder = factory(CompletedOrder::class)->create(['ecommerce_order_id' => rand(100,999), 'ecommerce_order_status' => 'not_approved']);
    }

    public function testOrderApprovalSuccess()
    {
        self::assertTrue($this->completedOrder->ecommerce_order_status == 'not_approved');

        $response = $this
            ->post('api/ecommerce/orders/' . $this->completedOrder->ecommerce_order_id . '/approve', []);

        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json['data']);
        self::assertTrue($json['data']['id'] == $this->completedOrder->id);
        self::assertTrue($json['data']['order_status'] == 'approved');
    }

    public function testOrderCannotBeApprovedWhenItHasBeenApprovedOrCancelled(): void
    {
        $this->completedOrder->ecommerce_order_status = CompletedOrder::ECOMMERCE_STATUS_CANCELED;
        $this->completedOrder->save();

        $response = $this
            ->post('api/ecommerce/orders/' . $this->completedOrder->ecommerce_order_id . '/approve', []);

        $response->assertStatus(500);

        $json = json_decode($response->getContent(), true);

        self::assertSame($json['message'], 'The order only could be approved when it is not approved');
    }
}
