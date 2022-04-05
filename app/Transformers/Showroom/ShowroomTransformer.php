<?php

namespace App\Transformers\Showroom;

use League\Fractal\TransformerAbstract;
use App\Models\Bulk\Parts\BulkUpload;

class ShowroomTransformer extends TransformerAbstract
{
    public function transform(BulkUpload $bulkUpload): array
    {
        return [
            'id' => $this->id,
            'manufacturer' => $this->manufacturer,
            'year' => $this->year,
            'is_visible' => $this->is_visible
        ];
    }
}
