<?php

namespace App\Repositories\Bulk\Inventory;

use App\Models\Bulk\Parts\BulkUpload;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;

/**
 * Describe the API for the repository of bulk download jobs
 *
 * @author Eczek
 */
interface BulkUploadRepositoryInterface
{
    /**
     * Gets a single record by provided params
     *
     * @param array $params
     * @return BulkUpload|Builder|null
     */
    public function get(array $params);

    /**
     * Gets all records by provided params
     *
     * @param array $params
     * @returns mixed list of BulkUpload
     */
    public function getAll(array $params);

}
