<?php


namespace App\Repositories\Bulk;


use App\Models\Bulk\Parts\BulkDownload;

interface BulkDownloadRepositoryInterface
{
    /**
     * Fetch a model by $id
     * @param $id
     * @return BulkDownload
     */
    public function find($id);

    /**
     * Find a download by token
     * @param $token
     * @return BulkDownload
     */
    public function findByToken($token);

    /**
     * @param array $params Array of values for the new row
     * @return mixed
     */
    public function create($params);

    /**
     * Set a row to completed status
     * @param $id
     * @return bool
     */
    public function setCompleted($id);
}
