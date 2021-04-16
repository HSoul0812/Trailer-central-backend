<?php

namespace App\Repositories\Bulk\Parts;

use App\Models\Bulk\Parts\BulkUpload;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;

/**
 * Describe the API for the repository of bulk download jobs
 *
 * @author Eczek
 */
interface BulkUploadRepositoryInterface extends MonitoredJobRepositoryInterface
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

    /**
     * Find a upload job by token
     *
     * @param string $token
     * @return BulkUpload
     */
    public function findByToken(string $token): BulkUpload;

    /**
     * Create a upload job by token
     *
     * @param array $params Array of values for the new row
     * @return BulkUpload
     */
    public function create(array $params): BulkUpload;

    /**
     * Stores CSV on S3 and returns its URL
     *
     * @param UploadedFile $file
     * @return string
     */
    public function storeCsv($file): string;
}
