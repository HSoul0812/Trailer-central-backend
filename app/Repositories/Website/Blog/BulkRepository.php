<?php

namespace App\Repositories\Website\Blog;

use App\Exceptions\NotImplementedException;
use App\Models\Bulk\Blog\BulkPostUpload;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Bulk\Blog\ProcessBulkUpload;

/**
 *
 * @author Eczek
 */
class BulkRepository implements BulkRepositoryInterface {

    public function create($params) {
        $csvKey = $this->storeCsv($params['csv_file']);

        $params['status'] = BulkPostUpload::PROCESSING;
        $params['import_source'] = $csvKey;

        $bulkUpload = BulkPostUpload::create($params);
        dispatch((new ProcessBulkUpload($bulkUpload->id))->onQueue('blog-posts'));
        return $bulkUpload;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return BulkPostUpload::where(array_key_first($params), current($params))->first();
    }

    public function getAll($params) {

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        return BulkPostUpload::where('dealer_id', $params['dealer_id'])->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $bulkUpload = BulkPostUpload::findOrFail($params['id']);
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
