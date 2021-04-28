<?php

declare(strict_types=1);

namespace App\Transformers\Integration\CVR;

use App\Models\Integration\CVR\CvrFile;
use League\Fractal\TransformerAbstract;

class CrvFileTransformer extends TransformerAbstract
{
    public function transform(CvrFile $file): array
    {
        return [
            'id' => $file->token,
            'status' => $file->status,
            'errors' => $file->result->errors ?? []
        ];
    }
}
