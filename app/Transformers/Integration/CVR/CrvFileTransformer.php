<?php

declare(strict_types=1);

namespace App\Transformers\Integration\CVR;

use App\Models\Integration\CVR\CvrFile;
use League\Fractal\TransformerAbstract;

class CrvFileTransformer extends TransformerAbstract
{
    public function transform(CvrFile $bulkUpload): array
    {
        return [
            'id' => $bulkUpload->token,
            'status' => $bulkUpload->status,
            'validation_errors' => $bulkUpload->result->validation_errors ?? []
        ];
    }
}
