<?php

namespace App\Services\Common;

interface RedisServiceInterface {
    /**
     * Validate an UUID before creating a floorplan payment
     * 
     * @param int $dealerId
     * @param string $paymentUUID
     * @return boolean
     */
    public function validatePaymentUUID(int $dealerId, string $paymentUUID);

    /**
     * Set a payment UUID for a dealer
     * 
     * @param int $dealerId
     * @param string $paymentUUID
     */
    public function setPaymentUUID(int $dealerId, string $paymentUUID);
}