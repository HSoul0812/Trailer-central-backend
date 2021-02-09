<?php

namespace App\Services\Common;

use Illuminate\Support\Facades\Redis;

const FLOORPLAN_PAYMENT_KEY_PREFIX = 'bulk_floorplan_payment_';

class RedisService implements RedisServiceInterface
{
    /**
     * @var Connection
     */
    private $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function validatePaymentUUID(int $dealerId, string $paymentUUID)
    {
        $bulkFloorplanPaymentKey = FLOORPLAN_PAYMENT_KEY_PREFIX . $dealerId;
        if ($this->redis->get($bulkFloorplanPaymentKey) === $paymentUUID) {
            return false;
        }

        return true;
    }

    public function setPaymentUUID(int $dealerId, string $paymentUUID)
    {
        $bulkFloorplanPaymentKey = FLOORPLAN_PAYMENT_KEY_PREFIX . $dealerId;
        $this->redis->set($bulkFloorplanPaymentKey, $paymentUUID);
    }
}
