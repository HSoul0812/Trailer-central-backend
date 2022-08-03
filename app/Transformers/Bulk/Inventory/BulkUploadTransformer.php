<?php

namespace App\Transformers\Bulk\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Bulk\Inventory\BulkUpload;

class BulkUploadTransformer extends TransformerAbstract
{
    public function transform(BulkUpload $bulkUpload): array
    {
        return [
            'id' => $bulkUpload->id,
            'status' => $bulkUpload->status,
            'import_source' => $bulkUpload->import_source,
            'validation_errors' => $bulkUpload->validation_errors
        ];
    }
}
