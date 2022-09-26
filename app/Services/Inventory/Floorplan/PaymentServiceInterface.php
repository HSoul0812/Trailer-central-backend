<?php

namespace App\Services\Inventory\Floorplan;

use Illuminate\Support\Collection;

interface PaymentServiceInterface
{
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

    /**
     * Create bulk floorplan payments for a dealer
     *
     * @param int $dealerId
     * @param array $payments
     * @param string $paymentUUID
     *
     * @return Collection
     */
    public function createBulk(int $dealerId, array $payments, string $paymentUUID);

    /**
     * Create a check expense for floorplan payments
     *
     * @param array $params
     *
     * @return Collection
     */
    public function create(array $params);

    /**
     * @param int $dealerId
     * @param string $checkNumber
     *
     * @return bool
     */
    public function checkNumberExists(int $dealerId, string $checkNumber): bool;
}
