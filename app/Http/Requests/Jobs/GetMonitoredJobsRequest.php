<?php

declare(strict_types=1);

namespace App\Http\Requests\Jobs;

use App\Http\Requests\Request;

class GetMonitoredJobsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer'
    ];
}
