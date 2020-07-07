<?php


namespace App\Repositories\Pos;


use App\Models\Pos\Sale;
use App\Repositories\Repository;
use App\Utilities\JsonApi\RequestQueryable;

interface SaleRepositoryInterface extends Repository, RequestQueryable
{
    /**
     * Find a single record
     * @param int $id primary key
     * @return Sale
     */
    public function find($id);

}
