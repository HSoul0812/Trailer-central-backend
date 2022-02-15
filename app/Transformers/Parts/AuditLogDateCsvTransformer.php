<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;

class AuditLogDateCsvTransformer extends TransformerAbstract
{
    public function transform(\stdClass $exportFile)
    {
        return [
            'export_path' => $exportFile->export_file
        ];
    }
}
