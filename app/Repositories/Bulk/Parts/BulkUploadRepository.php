<?php

namespace App\Repositories\Bulk\Parts;

use App\Exceptions\NotImplementedException;
use App\Models\Bulk\Parts\BulkUpload;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessBulkUpload;

/**
 *
 * @author Eczek
 */
class BulkUploadRepository implements BulkUploadRepositoryInterface {

    public function create($params) {
        $csvKey = $this->storeCsv($params['csv_file']);

        $params['status'] = BulkUpload::PROCESSING;
        $params['import_source'] = $csvKey;

        $bulkUpload = BulkUpload::create($params);
        dispatch((new ProcessBulkUpload($bulkUpload))->onQueue('parts'));
        return $bulkUpload;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return BulkUpload::where(array_key_first($params), current($params))->first();
    }

    public function getAll($params) {

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        return BulkUpload::where('dealer_id', $params['dealer_id'])->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $bulkUpload = BulkUpload::findOrFail($params['id']);
        $bulkUpload->fill($params);
        return $bulkUpload->save();
    }

    /**
     * Stores CSV on S3 and returns its URL
     *
     * @param InputFile $file
     * @return string
     */
    private function storeCsv($file) {
        $fileKey = Storage::disk('s3')->putFile(uniqid().'/'.$file->getClientOriginalName(), $file);
        return $fileKey;
    }
}
