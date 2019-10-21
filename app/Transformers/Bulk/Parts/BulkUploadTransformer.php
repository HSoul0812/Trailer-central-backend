<?php

namespace App\Transformers\Bulk\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Bulk\Parts\BulkUpload;

class BulkUploadTransformer extends TransformerAbstract
{
    public function transform(BulkUpload $bulkUpload)
    {                        
	 return [
             'id' => (int)$bulkUpload->id,
             'status' => $bulkUpload->status,
             'source_file' => $bulkUpload->import_source,
             'validation_errors' => $bulkUpload->getValidationErrors()
         ];
    }
}