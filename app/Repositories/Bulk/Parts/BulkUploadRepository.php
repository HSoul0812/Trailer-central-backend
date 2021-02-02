<?php

namespace App\Repositories\Bulk\Parts;

use App\Models\Bulk\Parts\BulkUpload;
use App\Repositories\Common\MonitoredJobRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessBulkUpload;

/**
 * Implementation for bulk upload repository
 *
 * @author Eczek
 */
class BulkUploadRepository extends MonitoredJobRepository implements BulkUploadRepositoryInterface
{
    /**
     * @param string $token
     * @return BulkUpload
     */
    public function findByToken(string $token): BulkUpload
    {
        return BulkUpload::where('token', $token)->get()->first();
    }

    public function create(array $params): BulkUpload
    {
        $csvKey = $this->storeCsv($params['payload']['csv_file']);

        unset($params['payload']['csv_file']);

        $params['status'] = BulkUpload::PROCESSING;
        $params['payload']['import_source'] = $csvKey;

        $bulkUpload = BulkUpload::create($params);
        dispatch((new ProcessBulkUpload($bulkUpload))->onQueue('parts'));

        return $bulkUpload;
    }

    /**
     * Gets a single record by provided params
     *
     * @param array $params
     * @return BulkUpload|Builder|null
     */
    public function get(array $params)
    {
        return BulkUpload::where(array_key_first($params), current($params))->first();
    }

    /**
     * Gets all records by provided params
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getAll(array $params): LengthAwarePaginator
    {
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        $query = BulkUpload::select('*');

        if (isset($params['dealer_id'])) {
            $query->where('dealer_id', $params['dealer_id']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Stores CSV on S3 and returns its URL
     *
     * @param UploadedFile $file
     * @return string
     */
    public function storeCsv($file): string
    {
        $path = uniqid() . '/' . $file->getClientOriginalName();

        return Storage::disk('s3')->putFile($path, $file, 'public');
    }
}
