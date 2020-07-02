<?php


namespace App\Repositories\CRM\Payment;


use App\Models\CRM\Account\Payment;
use App\Repositories\Repository;
use App\Utilities\JsonApi\RequestQueryable;

interface PaymentRepositoryInterface extends Repository, RequestQueryable
{
    /**
     * Find a single record
     * @param int $id primary key
     * @return Payment
     */
    public function find($id);

}
