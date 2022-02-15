<?php

namespace App\Transformers\Bulk\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Bulk\Parts\BulkUpload;

class BulkUploadTransformer extends TransformerAbstract
{
    public function transform(BulkUpload $bulkUpload): array
    {
        return [
            'id' => $bulkUpload->id,
            'status' => $bulkUpload->status,
            'source_file' => $bulkUpload->source_file,
            'validation_errors' => $bulkUpload->validation_errors
        ];
    }
}
