<?php

namespace App\Services\Inventory\Floorplan;

use Illuminate\Support\Facades\Redis;
use App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface;

class PaymentService implements PaymentServiceInterface
{

    const FLOORPLAN_PAYMENT_KEY_PREFIX = 'bulk_floorplan_payment_';

    /**
     * @var Connection
     */
    private $redis;

    /**
     * @var PaymentRepositoryInterface
     */
    private $payment;

    public function __construct(PaymentRepositoryInterface $payment)
    {
        $this->redis = Redis::connection();
        $this->payment = $payment;
    }

    public function validatePaymentUUID(int $dealerId, string $paymentUUID)
    {
        $bulkFloorplanPaymentKey = self::FLOORPLAN_PAYMENT_KEY_PREFIX . $dealerId;
        if ($this->redis->get($bulkFloorplanPaymentKey) === $paymentUUID) {
            return false;
        }

        return true;
    }

    public function setPaymentUUID(int $dealerId, string $paymentUUID)
    {
        $bulkFloorplanPaymentKey = self::FLOORPLAN_PAYMENT_KEY_PREFIX . $dealerId;
        $this->redis->set($bulkFloorplanPaymentKey, $paymentUUID, 'EX', 3600);
    }

    public function createBulk(int $dealerId, array $payments, string $paymentUUID)
    {
        $payments = $this->payment->createBulk($payments);
        $this->setPaymentUUID($dealerId, $paymentUUID);

        return $payments;
    }
}
