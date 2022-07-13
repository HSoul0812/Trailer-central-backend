<?php

namespace App\Repositories\Bulk\Inventory;

use App\Repositories\Bulk\Inventory\BulkUploadRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Bulk\Inventory\BulkUpload;
use App\Jobs\Bulk\Inventory\ProcessBulkUpload;
use Illuminate\Support\Facades\Storage;


/**
 * Class BulkUploadRepository
 * @params App\Repositories\Bulk\Inventory
 */
class BulkUploadRepository implements BulkUploadRepositoryInterface {

    /**
     * @param $params
     * @return mixed
     */
    public function create($params) {
        $csvKey = $this->storeCsv($params['csv_file']);

        $params['status'] = BulkUpload::PROCESSING;
        $params['import_source'] = $csvKey;

        $bulkUpload = BulkUpload::create($params);
        dispatch((new ProcessBulkUpload($bulkUpload->id))->onQueue('inventory'));

        return $bulkUpload;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @return \App\Models\Bulk\Parts\BulkUpload|\Illuminate\Database\Query\Builder|null
     */
    public function get($params) {
        return BulkUpload::where(array_key_first($params), current($params))->first();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function getAll($params) {

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        return BulkUpload::where('dealer_id', $params['dealer_id'])->paginate($params['per_page'])->appends($params);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function update($params) {
        $bulkUpload = BulkUpload::findOrFail($params['id']);
        $bulkUpload->fill($params);
        return $bulkUpload->save();
    }

    /**
     * Stores CSV on S3 and returns its URL
     *
     * @param $file
     * @return string
     */
    private function storeCsv($file) {
        $fileKey = Storage::disk('s3')->putFile(uniqid().'/'.$file->getClientOriginalName(), $file, 'public');
        return $fileKey;
    }
}
