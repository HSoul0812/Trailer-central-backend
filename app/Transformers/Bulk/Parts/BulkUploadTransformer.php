<?php

namespace App\Transformers\Bulk\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Bulk\Parts\BulkUpload;

class BulkUploadTransformer extends TransformerAbstract
{
    public function transform(BulkUpload $bulkUpload): array
    {
        return [
            'id' => $bulkUpload->token,
            'status' => $bulkUpload->status,
            'source_file' => $bulkUpload->payload->import_source,
            'validation_errors' => $bulkUpload->getValidationErrors()
        ];
    }
}
