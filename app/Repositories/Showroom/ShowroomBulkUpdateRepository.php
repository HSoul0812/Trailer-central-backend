<?php

namespace App\Repositories\Showroom;

use Exception;
use App\Exceptions\NotImplementedException;

use App\Models\Showroom\Showroom;
use App\Models\Inventory\InventoryMfg;

use App\Jobs\Showroom\ShowroomBulkUpdateYear;
use App\Jobs\Showroom\ShowroomBulkUpdateVisibility;

/**
 * Class ShowroomBulkUpdateRepository
 * @package App\Repositories\Showroom
 */
class ShowroomBulkUpdateRepository implements ShowroomBulkUpdateRepositoryInterface
{

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException();
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException();
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
     * @return mixed
     */
    public function get($params)
    {
        return Showroom::where($params)->get();
    }

    /**
     * @return mixed
     */
    public function getAll($params)
    {
        return InventoryMfg::orderBy('name', 'asc')->get();
    }

    /**
     * @param $manufacturer
     * @param $params
     * @return mixed
     */
    public function bulkUpdate($manufacturer, $params): bool
    {
        return $manufacturer->update($params);
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function bulkUpdateYear($params)
    {
        return dispatch((new ShowroomBulkUpdateYear($params))->onQueue('manufacturers'));
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function bulkUpdateVisibility($params)
    {
        return dispatch((new ShowroomBulkUpdateVisibility($params))->onQueue('manufacturers'));
    }
}
