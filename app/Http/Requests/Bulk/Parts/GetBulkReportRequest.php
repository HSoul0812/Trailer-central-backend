<?php

declare(strict_types=1);

namespace App\Http\Requests\Bulk\Parts;

use App\Http\Requests\Request;

class GetBulkReportRequest extends Request
{
    public function getRules(): array
    {
        return [
            'token' => 'required|uuid'
        ];
    }
}
