<?php


namespace App\Repositories\Bulk;


interface BulkDownloadRepositoryInterface
{
    /**
     * Fetch a model by $id
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * @param array $params Array of values for the new row
     * @return mixed
     */
    public function create($params);

    /**
     * Set a row to completed status
     * @param $id
     * @return mixed
     */
    public function setCompleted($id);
}
