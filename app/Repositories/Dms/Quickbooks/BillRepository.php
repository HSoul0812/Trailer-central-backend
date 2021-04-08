<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Quickbooks\Bill;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class BillRepository
 * @package App\Repositories\Dms\Quickbooks
 */
class BillRepository implements BillRepositoryInterface
{
    /**
     * @param $params
     * @return Bill
     */
    public function create($params): Bill
    {
        $bill = new Bill($params);
        $bill->save();

        return $bill;
    }

    /**
     * @param array $params
     * @return Bill
     *
     * @throws ModelNotFoundException
     */
    public function update($params): Bill
    {
        $bill = Bill::findOrFail($params['id']);
        $bill->fill($params)->save();

        return $bill;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function getAll($params)
    {
        throw new NotImplementedException;
    }
}
