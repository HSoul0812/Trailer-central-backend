<?php

declare(strict_types=1);

namespace App\Http\Requests\Bulk\Parts;

use App\Http\Requests\Request;

class CreateBulkDownloadRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer',
        'token' => 'uuid'
    ];
}
