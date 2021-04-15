<?php

declare(strict_types=1);

namespace App\Http\Requests\Integration\CVR;

use App\Http\Requests\Request;

class SendFileRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'required|integer',
            'token' => 'uuid|nullable',
            'document' => 'file|required|mimes:zip'
        ];
    }
}
