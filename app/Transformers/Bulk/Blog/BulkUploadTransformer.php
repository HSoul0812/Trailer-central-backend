<?php

namespace App\Transformers\Bulk\Blog;

use App\Models\Bulk\Blog\BulkPostUpload;
use League\Fractal\TransformerAbstract;

class BulkUploadTransformer extends TransformerAbstract
{
    public function transform(BulkPostUpload $bulkPostUpload): array
    {

        return [
            'id' => $bulkPostUpload->id,
            'status' => $bulkPostUpload->status,
            'source_file' => $bulkPostUpload->source_file,
            'website_id' => $bulkPostUpload->website_id,
            'validation_errors' => $bulkPostUpload->validation_errors,
        ];
    }
}
