<?php

declare(strict_types=1);

namespace App\Http\Requests\Jobs;

use App\Http\Requests\Request;

class ReadMonitoredJobsRequest extends Request
{
    public function getRules(): array
    {
        return [
            'token' => 'required|uuid'
        ];
    }
}
