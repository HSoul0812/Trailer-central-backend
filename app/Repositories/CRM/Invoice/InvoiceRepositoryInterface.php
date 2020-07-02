<?php


namespace App\Repositories\CRM\Invoice;


use App\Models\CRM\Account\Invoice;
use App\Repositories\Repository;
use App\Utilities\JsonApi\RequestQueryable;

interface InvoiceRepositoryInterface extends Repository, RequestQueryable
{
    /**
     * Find a single record
     * @param int $id primary key
     * @return Invoice
     */
    public function find($id);

}
